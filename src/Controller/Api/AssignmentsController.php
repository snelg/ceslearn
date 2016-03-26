<?php
namespace App\Controller\Api;

use App\Controller\Api\AppController;

/**
 * Assignments Controller
 *
 * @property \App\Model\Table\CanvasTable $Canvas
 * @property \App\Model\Table\BrightspaceTable $Brightspace
 */
class AssignmentsController extends AppController
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
        $assignments = $this->Canvas->assignments(14);
        if ($this->request->session()->read('Brightspace.Auth')) {
            $this->loadModel('Brightspace');
            $assignments = array_merge($assignments, $this->Brightspace->events(6784));
        }
        usort($assignments, function ($a, $b) {
            $dueA = empty($a->start) ? null : $a->start;
            $dueB = empty($b->start) ? null : $b->start;
            if ($dueA == $dueB) {
                return 0;
            }
            return ($dueA < $dueB) ? -1 : 1;
        });
        $this->set('output', $assignments);
    }
}
