<?php
namespace App\Controller\Api;

use App\Controller\Api\AppController;

/**
 * Notifications Controller
 *
 * @property \App\Model\Table\CanvasTable $Canvas
 */
class NotificationsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null|void
     */
    public function index()
    {
        session_write_close(); //No writing will occur after this point, so do not block parallel requests
        $this->loadModel('Canvas');
        $assignments = $this->Canvas->notifications();
        $this->set('output', $assignments);
    }
}
