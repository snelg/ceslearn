<?php
namespace App\Controller\Api;

use App\Controller\AppController as Controller;
use Cake\Core\Configure;

class AppController extends Controller
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->set('_serialize', 'output');
        if (Configure::read('debug')) {
            //Provide dummy dump view + debug info for data, not just JSON output
            $this->viewBuilder()->templatePath('Api');
        }
    }
}
