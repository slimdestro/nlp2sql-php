<?php
/**
 * package nlp2sql-php
 * ChatGPT3 client
 * @ author: @slimdestro
 */

use OpenAI\Api\Authentication\BearerAuthentication;
use OpenAI\Api\Request\CompletionRequest;
use OpenAI\Client;
use Dotenv\Dotenv;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class ChatGPT3 {
    private $client;
    private $conn;

    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $apiKey = $_ENV['OPENAI_API_KEY'];
        $auth = new BearerAuthentication($apiKey);
        $this->client = new Client($auth);

        $dbConfig = [
            'url' => $_ENV['DATABASE_URL']
        ];
        $this->conn = DriverManager::getConnection($dbConfig);
    }

    public function ask($prompt) {
        $model = $_ENV['OPENAI_MODEL'];
        $request = new CompletionRequest();
        $request->setModel($model);
        $request->setPrompt($prompt);
        $response = $this->client->createCompletion($request);

        return $response->getChoices()[0]->getText();
    }

    public function parser($text) {
        $prompt = "Convert this natural language query to SQL: \"$text\"\n\nSQL query:";
        $sql = $this->ask($prompt);

        try {
            $stmt = $this->conn->executeQuery($sql);
            return $sql;
        } catch (Exception $e) {
            return 'Error: '.$e->getMessage();
        }
    }
}