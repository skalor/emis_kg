<?php
namespace MonGeneratedStatisticReports\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

class SignApiComponent extends Component
{
    private $domain = 'https://cdsapi.srs.kg/api';
    private $token = 'rkdhlSgRpDXYc3Xui7w+MX37SHvSTUQe';
    private $logModel = 'ApiLogs.ApiLogs';
    private $controllerName = '';

    private function post(string $urlSuffix, array $params = [])
    {
        $ch = curl_init();
        $fields = [
            CURLOPT_URL => $this->domain . '/' . $urlSuffix,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json;charset=utf-8',
                $this->token ? 'Authorization: Bearer ' . $this->token : null
            ],
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true
        ];

        $params = json_encode($params, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $fields[CURLOPT_POSTFIELDS] = $params;

        curl_setopt_array($ch, $fields);
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        $this->logging($urlSuffix, $params, $body, curl_getinfo($ch, CURLINFO_RESPONSE_CODE));
        curl_close($ch);

        return $body;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setDomain(string $domain)
    {
        $this->domain = $domain;

        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }

    public function getLogModel()
    {
        return $this->logModel;
    }

    public function setLogModel(string $model)
    {
        $this->logModel = $model;

        return $this;
    }

    public function getControllerName()
    {
        return $this->controllerName;
    }

    public function setControllerName(string $name)
    {
        $this->controllerName = $name;

        return $this;
    }

    public function getAuthMethod(string $personIdnp, string $organisationInn, ?string $urlSuffix = 'get-auth-method')
    {
        return $this->post($urlSuffix, [
            'personIdnp' => $personIdnp,
            'organizationInn' => $organisationInn
        ]);
    }

    public function auth(string $personIdnp, string $organisationInn, string $byPin, ?string $urlSuffix = 'account/auth')
    {
        return $this->post($urlSuffix, [
            'personIdnp' => $personIdnp,
            'organizationInn' => $organisationInn,
            'byPin' => $byPin
        ]);
    }

    public function getCertInfo(string $userToken, ?string $urlSuffix = 'get-cert-info')
    {
        return $this->post($urlSuffix, [
            'userToken' => $userToken
        ]);
    }

    public function getForHash(string $hash, string $userToken, ?string $urlSuffix = 'get-sign/for-hash')
    {
        return $this->post($urlSuffix, [
            'hash' => $hash,
            'userToken' => $userToken
        ]);
    }

    public function checkForHash(string $hash, string $signBase64, ?string $urlSuffix = 'check-sign/for-hash')
    {
        return $this->post($urlSuffix, [
            'hash' => $hash,
            'signBase64' => $signBase64
        ]);
    }

    public function logging(string $action, string $params, string $response, string $responseStatus)
    {
        if (!$this->logModel || !$this->controllerName) {
            return null;
        }

        $table = TableRegistry::get($this->logModel);
        if (!$table) {
            return null;
        }

        $entity = $table->newEntity();
        $entity->controller = $this->controllerName;
        $entity->action = $action;
        $entity->params = $params;
        $entity->response = $response;
        $entity->status = $responseStatus;

        return $table->save($entity);
    }
}
