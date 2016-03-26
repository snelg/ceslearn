<?php
namespace App\Model\Table;

use Cake\Database\Log\LoggedQuery;
use Cake\Network\Http\Client;
use Cake\Network\Request;
use Cake\ORM\Table;
use D2LAppContextFactory;
use D2LHostSpec;
use D2LUserContext;

class BrightspaceTable extends Table
{
    protected $_client;
    protected $_valenceContext;

    public static function defaultConnectionName()
    {
        return 'brightspace';
    }

    public function initialize(array $config)
    {
        $this->table(false);
    }

    protected function client()
    {
        if (!$this->_client) {
            $config = $this->connection()->config();
            if (empty($config['headers']['Authorization'])) {
                //TODO: real OAuth2 here
            }
            $this->_client = new Client($config);
        }
        return $this->_client;
    }

    protected function valenceContext()
    {
        if (!$this->_valenceContext) {
            $session = Request::createFromGlobals()->session();
            $valenceUser = $session->read('Brightspace.Auth');
            if (empty($valenceUser)) {
                return null;
            }

            $config = $this->connection()->config();
            $authContextFactory = new D2LAppContextFactory();
            $authContext = $authContextFactory->createSecurityContext($config['valenceInfo']['appId'], $config['valenceInfo']['appKey']);

            // Create userContext
            $hostSpec = new D2LHostSpec($config['host'], $config['port'], $config['scheme']);
            $this->_valenceContext = $authContext->createUserContextFromHostSpec($hostSpec, $valenceUser['userId'], $valenceUser['userKey']);
        }
        return $this->_valenceContext;
    }

    public function get($url, $options = []) {
        $q = new LoggedQuery();
        $q->query = $url;

        $authUrl = $this->valenceContext()->createAuthenticatedUri($url, 'GET');

        //Something's not quite working with Cake client->get, so manually using curl
        $ch = curl_init();
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_URL            => $authUrl,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO         => CORE_PATH . 'config' . DIRECTORY_SEPARATOR . 'cacert.pem'
        ];
        curl_setopt_array($ch, $options);

        $start = microtime(true);
        $responseRaw = curl_exec($ch);
        $q->took = intval(1000.0 * (microtime(true) - $start));

        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        $responseCode = $this->valenceContext()->handleResult($responseRaw, $httpCode, $contentType);

        if ($responseCode == D2LUserContext::RESULT_OKAY) {
            $response = json_decode($responseRaw);
            $q->numRows = count($response);
        } else {
            $q->error = "$httpCode: $responseRaw";
            $response = null;
        }
        $this->_connection->logger()->log($q);

        return $response;
    }

    public function events($courseId)
    {
        $config = $this->connection()->config();
        $eventsRaw = $this->get("/d2l/api/le/{$config['valenceInfo']['LE_Version']}/{$courseId}/calendar/events/");

        //reformat to universal format
        $events = [];
        foreach ($eventsRaw as $event) {
            $startTime = 0;
            if (!empty($event->StartDateTime)) {
                $startTime = strtotime($event->StartDateTime);
            } elseif (!empty($event->StartDay)) {
                $startTime = strtotime($event->StartDay);
            }
            $events[] = (object)[
                'id' => 'brightspace-' . $event->CalendarEventId,
                'source' => 'Brightspace',
                'start' => $startTime,
                'title' => $event->Title,
                'description' => $event->Description,
                'class_name' => $event->OrgUnitName,
                'class_code' => $event->OrgUnitCode
            ];
        }
        return $events;
    }
    /**
     * Get "conversations" for current user
     */
    public function notifications()
    {
        $notifications = $this->get("/api/v1/conversations?as_user_id=sis_user_id:mtm49");
        return $notifications;
    }

}
