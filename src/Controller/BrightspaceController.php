<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Routing\Router;
use D2LAppContextFactory;
use D2LHostSpec;

/**
 * Brightspace Controller
 *
 * @property \App\Model\Table\BrightspaceTable $Brightspace
 */
class BrightspaceController extends AppController
{

    /**
     * Auth method
     *
     * @return \Cake\Network\Response|null|void
     */
    public function auth()
    {
        if ($this->request->query('x_a') && $this->request->query('x_b')) {
            $this->request->session()->write('Brightspace.Auth', [
                'userId' => $this->request->query('x_a'),
                'userKey' => $this->request->query('x_b')]);
        }

        if ($this->request->session()->read('Brightspace.Auth')) {
            return $this->redirect('/');
        }

        //Need to get full url, including server
        $returnUrl = Router::url($this->request->here(), true);
        $config = $this->Brightspace->connection()->config();
        $appId = $config['valenceInfo']['appId'];
        $appKey = $config['valenceInfo']['appKey'];

        $authContextFactory = new D2LAppContextFactory();
        $authContext = $authContextFactory->createSecurityContext($appId, $appKey);
        $hostSpec = new D2LHostSpec($config['host'], $config['port'], $config['scheme']);
        $url = $authContext->createUrlForAuthenticationFromHostSpec($hostSpec, $returnUrl);
        $this->redirect($url);
    }

    public function deAuth()
    {
        $this->request->session()->delete('Brightspace.Auth');
        $this->redirect('/');
    }
}
