<?php

use App\Classes\EmailRepository;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Index for Email Service
 */
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('Email Service. Routes are in progress.');

    return $response;
})->setName("email-index");

/**
 * View the details of an email
 */
$app->get('/email/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');

    $repository = new EmailRepository($this->DB);
    $email = $repository->retrieve($id);

    return $response->withJson($email);
})->setName("email-view");

/**
 * Cancel the sending of a pending email
 */
$app->delete('/email/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');

    $repository = new EmailRepository($this->DB);
    $result = $repository->cancel($id);

    return $response->withStatus(204);
})->setName("email-cancel");

/**
 * Send an Email Asynchronously
 */
$app->post('/send', function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    $email = EmailRepository::factory(
        $params['to'],
        $params['from'],
        $params['subject'],
        $params['message']
    );

    $repository = new EmailRepository($this->DB);
    $id = $repository->store($email, EmailRepository::STATUS_PENDING);

    $result = $this->Mailer->send($email);
    if($result) {
        $repository->updateStatus($id, EmailRepository::STATUS_SENT);
    }

    return $response->withJson($id);
})->setName("email-send");

/**
 * Add an email to a queue
 */
$app->post('/queue', function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    $email = EmailRepository::factory(
        $params['to'],
        $params['from'],
        $params['subject'],
        $params['message']
    );

    $repository = new EmailRepository($this->DB);
    $id = $repository->store($email, EmailRepository::STATUS_QUEUED);

    return $response->withJson($id);
})->setName("email-queue");
