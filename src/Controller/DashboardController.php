<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Dashboard Controller
 *
 * @property \App\Model\Table\GoalsTable $Goals
 * @property \App\Model\Table\NotesTable $Notes
 * @property \App\Model\Table\CanvasTable $Canvas
 */
class DashboardController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null|void
     */
    public function index()
    {
        $this->loadModel('Goals');
        $this->loadModel('Notes');
        $this->set('goals', $this->Goals->findCategorized());
        $this->set('note', $this->Notes->find()->first());
    }
}
