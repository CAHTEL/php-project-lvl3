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
    $params = ['flash' => [], 'url' => ''];
    return $this->get('renderer')->render($response, 'index.html', $params);
});

$app->post('/urls', function ($request, $response, $args) {
    $url = $request->getParsedBodyParam('url')['name'];
    $v = new Valitron\Validator(array('name' => $url));
    $v->rule('required', 'name');
    $v->rule('url', 'name');
    if($v->validate()) {
        $time = Carbon::now();
        $parseUrl = parse_url($url);
        $normalUrl = implode( '',[$parseUrl['scheme'], '://', $parseUrl['host']]);
        try {
            $pdo =Connection::get()->connect();
            $newInsert = new Insert($pdo);
            $insert = $newInsert->insertLabel($normalUrl, $time);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
        return $response->withRedirect('/urls/' . $insert);
    }
    $this->get('flash')->addMessage('error', 'Некорректный URL');
    $messages = $this->get('flash')->getMessages();
    $params = ['url' => $url, 'flash' => $messages];
    return $this->get('renderer')->render($response, 'index.html', $params);
});

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) {
    $url_id = $args['url_id'];
    $time = Carbon::now();
    try {
        $pdo =Connection::get()->connect();
        $selectId = new Select($pdo);
        $url = $selectId->selectSql("SELECT * FROM urls WHERE id = {$url_id}")[0];
        $document = new Document($url['name'], true);
        $h1 = optional($document->first('h1'))->text();
        $title = optional($document->first('title'))->text();
        $description = optional($document->first('meta[name=description]'))->getAttribute('content');
        $client = new Client();
        $res = $client->request('GET', $url['name'], [
        'auth' => ['user', 'pass']
        ]);
        $statusCode = $res->getStatusCode();
        $newInsert = new Insert($pdo);
        $insert = $newInsert->insertLabel2($url_id, $statusCode, $h1, $title, $description, $time);
    } catch (\PDOException $e) {
        echo $e->getMessage();
    }
    $this->get('flash')->addMessage('success', ' Страница успешно проверена');
    return $response->withRedirect('/urls/' . $url_id);
});

$app->get('/urls', function ($request, $response, $args) {
    try {
        $pdo =Connection::get()->connect();
        $newSelect = new Select($pdo);
        $select = $newSelect->selectSql("SELECT * FROM urls ORDER BY id DESC");
    } catch (\PDOException $e) {
        echo $e->getMessage();
    }
    $res = [];
    foreach ($select as $sel) {
        $s = $newSelect->selectSql("select * from url_checks where url_id = {$sel['id']} order by created_at desc limit 1");
        if (count($s) > 0) {
            $res[] =['id' => $sel['id'], 'name' => $sel['name'], 'created_at' => $s[0]['created_at'], 'status_code' => $s[0]['status_code']];
        }
        else {
            $res[] = ['id' => $sel['id'], 'name' => $sel['name'], 'created_at' => '', 'status_code' => ''];
        }
    }

    $params = ['urls' => $res];
    return $this->get('renderer')->render($response, 'show.html', $params);
});


$app->get('/urls/{id:[0-9]+}', function ($request, $response, $args) {
    $id = $args['id'];
    try {
        $pdo =Connection::get()->connect();
        $newSelect = new Select($pdo);
        $select = $newSelect->selectSql("SELECT * FROM urls WHERE id = {$id}");
        $select2 = $newSelect->selectSql("SELECT * FROM url_checks WHERE url_id = {$id}");
    } catch (\PDOException $e) {
        echo $e->getMessage();
    }
    $messages = $this->get('flash')->getMessages();
    $params = ['url' => $select[0], 'flash' => $messages, 'url_checks' => $select2];
    return $this->get('renderer')->render($response, 'check.html', $params);
});

$app->run();
