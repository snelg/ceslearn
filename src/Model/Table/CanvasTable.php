<?php
namespace App\Model\Table;

use Cake\Database\Log\LoggedQuery;
use Cake\Network\Http\Client;
use Cake\ORM\Table;

class CanvasTable extends Table
{
    protected $_client;

    public static function defaultConnectionName()
    {
        return 'canvas';
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

    public function get($url, $options = []) {
        $q = new LoggedQuery();
        $q->query = $url;

        $start = microtime(true);
        $responseRaw = $this->client()->get($url);
        $q->took = intval(1000.0 * (microtime(true) - $start));
        if (empty($responseRaw) || !$responseRaw->isOk()) {
            $q->error = 'No response';
        }

        $response = $responseRaw->body('json_decode');

        $q->numRows = count($response);
        $this->_connection->logger()->log($q);

        return $response;
    }

    public function assignments($courseId)
    {
        $assignments = $this->get("/api/v1/courses/{$courseId}/assignments?per_page=100");
        foreach ($assignments as &$assignment) {
            $assignment->id = 'canvas-' . $assignment->id;
            $assignment->source = 'Canvas';
            $assignment->start = empty($assignment->due_at) ? 0 : strtotime($assignment->due_at);
            $assignment->url = empty($assignment->html_url) ? '' : $assignment->html_url;
            $assignment->title = $assignment->name;
        }
        return $assignments;
    }

    /**
     * Get "conversations" for current user... well, specifically for user mtm49 for now
     */
    public function notifications()
    {
        $notifications = $this->get("/api/v1/conversations?as_user_id=sis_user_id:mtm49");
        return $notifications;
    }

}
