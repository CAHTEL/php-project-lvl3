<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use Hexlet\Code\Connection;
use Hexlet\Code\Insert;
use Hexlet\Code\Select;
use Carbon\Carbon;
use Valitron\Validator as V;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use DiDom\Document;
use DiDom\Query;

session_start();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response, $args) {
    $params = ['url' => '', 'flash' => []];
    $databaseUrl = parse_url($_ENV['DATABASE_URL']);
    return $this->get('renderer')->render($response, 'index.html', $params);
});

$app->post('/urls', function ($request, $response, $args) {
    $url = $request->getParsedBodyParam('url')['name'];
    $validator = new Valitron\Validator(array('URL' => $url));
    $validator->rule('required', 'URL')->message('{field} не должен быть пустым');
    $validator->rule('url', 'URL')->message('Некорректный {field}');
    if ($validator->validate()) {
        $time = Carbon::now();
        $parseUrl = parse_url($url);
        $normalUrl = implode('', [$parseUrl['scheme'], '://', $parseUrl['host']]);
        try {
            $pdo = Connection::get()->connect();
            $select = new Select($pdo);
            $urlInTable = $select->selectSql("SELECT * FROM urls WHERE name = ? LIMIT 1", [$normalUrl]);
            if ($urlInTable) {
                $this->get('flash')->addMessage('success', 'Страница уже существует');
                return $response->withRedirect('/urls/' . $urlInTable[0]['id']);
            }
            $insert = new Insert($pdo);
            $id = $insert->insertSql("INSERT INTO urls (name, created_at) VALUES(?, ?)", [$normalUrl, $time]);
            $redirect = "/urls/{$id}";
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        if (isset($redirect)) {
            $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
            return $response->withRedirect($redirect);
        } else {
            return $response->withRedirect('/');
        }
    }
    $params = ['url' => $url, 'flash' => $validator->errors()];
    return $this->get('renderer')->render($response->withStatus(422), 'index.html', $params);
});

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) {
    $url_id = $args['url_id'];
    $time = Carbon::now();
        $pdo = Connection::get()->connect();
        $selectId = new Select($pdo);
        $url = $selectId->selectSql("SELECT * FROM urls WHERE id = {$url_id}")[0];
    try {
        $document = new Document($url['name'], true);
        $h1 = optional($document->first('h1'))->text();
        $title = optional($document->first('title'))->text();
        $description = optional($document->first('meta[name=description]'))->getAttribute('content');
        $client = new Client(['base_uri' => $url['name'],
            'timeout'  => 3.0,
        ]);
        $client->request('GET', '', ['connect_timeout' => 3.14]);
        $res = $client->get($url['name']);
    } catch (\Throwable $e) {
            $this->get('flash')->addMessage('error', 'Произошла ошибка при проверке, не удалось подключиться');
            return $response->withRedirect('/urls/' . $url_id);
    }
        $statusCode = $res->getStatusCode();
        $insert = new Insert($pdo);
        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
        VALUES(?, ?, ?, ?, ?, ?)";
        $insertInTable = $insert->insertSql($sql, [$url_id, $statusCode, $h1, $title, $description, $time]);
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
        return $response->withRedirect('/urls/' . $url_id);
});

$app->get('/urls', function ($request, $response, $args) {
    try {
        $pdo = Connection::get()->connect();
        $newSelect = new Select($pdo);
        $select = $newSelect->selectSql("SELECT * FROM urls ORDER BY id DESC");
    } catch (\PDOException $e) {
        echo $e->getMessage();
    }
    if (isset($select) && isset($newSelect)) {
        $res = [];
        foreach ($select as $sel) {
            $s = $newSelect->selectSql("select * from url_checks where url_id
            = {$sel['id']} order by created_at desc limit 1");
            if (count($s) > 0) {
                    $res[] = ['id' => $sel['id'], 'name' => $sel['name'],
                    'created_at' => $s[0]['created_at'], 'status_code' => $s[0]['status_code']];
            } else {
                    $res[] = ['id' => $sel['id'], 'name' => $sel['name'], 'created_at' => '', 'status_code' => ''];
            }
        }
        $params = ['urls' => $res];
        return $this->get('renderer')->render($response, 'show.html', $params);
    } else {
        echo 'Ошибка';
    }
});

$app->get('/urls/{id:[0-9]+}', function ($request, $response, $args) {
    $id = $args['id'];
    try {
        $pdo = Connection::get()->connect();
        $select = new Select($pdo);
        $url = $select->selectSql("SELECT * FROM urls WHERE id = {$id}");
        $checks = $select->selectSql("SELECT * FROM url_checks WHERE url_id = {$id}");
    } catch (\PDOException $e) {
        echo $e->getMessage();
    }
    if (isset($url) && isset($checks)) {
        $messages = $this->get('flash')->getMessages();
        $params = ['url' => $url[0], 'flash' => $messages, 'url_checks' => $checks];
        return $this->get('renderer')->render($response, 'check.html', $params);
    } else {
        echo 'Error';
    }
});

$app->run();
