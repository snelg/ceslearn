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
        $this->loadModel('Canvas');
        $this->set('goals', $this->Goals->findCategorized());
        $this->set('note', $this->Notes->find()->first());

        $assignments = $this->Canvas->assignments(14);
        $upcomingAssignments = [];
        $notifications = $this->Canvas->notifications(451);
        foreach ($assignments as $assignment) {
            if (empty($assignment->due_at) || $assignment->due_at < time()) {
                continue;
            }
            $upcomingAssignments[] = $assignment;
            if (count($upcomingAssignments) == 5) {
                break;
            }
        }
        $this->set(compact('assignments', 'upcomingAssignments', 'notifications'));
    }
}
