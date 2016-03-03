<?php
namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Network\Http\Client;
use Cake\ORM\Table;

class CanvasTable extends Table
{
    protected $_client;
    protected $_clientConfig;

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

    public function assignments($courseId)
    {
        $response = $this->client()->get("/api/v1/courses/{$courseId}/assignments?per_page=100");
        $assignments = $response->body('json_decode');
        if (empty($assignments)) {
            return [];
        }
        foreach ($assignments as &$assignment) {
            $assignment->due_at = empty($assignment->due_at) ? 0 : strtotime($assignment->due_at);
        }
        usort($assignments, function ($a, $b) {
            $dueA = empty($a->due_at) ? null : $a->due_at;
            $dueB = empty($b->due_at) ? null : $b->due_at;
            if ($dueA == $dueB) {
                return 0;
            }
            return ($dueA < $dueB) ? -1 : 1;
        });
        return $assignments;
    }
    /**
     * Get "conversations" for current user
     */
    public function notifications()
    {
        $response = $this->client()->get("/api/v1/conversations");
        $notifications = $response->body('json_decode');
        
        return $notifications;
    }

}
