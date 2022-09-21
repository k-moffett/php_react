<?php

namespace App\Controller;

use Cake\Validation\Validator;

class ArticlesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
    }

    public function index()
    {
        $articles = $this->Paginator->paginate($this->Articles->find());
        $this->set(compact('articles'));
    }

    public function view($slug)
    {
        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        $this->set(compact('article'));
    }

    public function add()
    {
        $article = $this->Articles->newEmptyEntity();
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            $article->user_id = 1;

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.'));
        }
        $this->set('article', $article);
    }

    public function edit($slug)
    {
        $article = $this->Articles
            ->findBySlug($slug)
            ->firstOrFail();

        if ($this->request->is(['post', 'put'])) {
            $this->Articles->patchEntity($article, $this->request->getData());
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to update your article.'));
        }

        $this->set('article', $article);
    }

    public function delete($slug)
    {
        $this->request->allowMethod(['post', 'delete']);

        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The {0} article has been deleted.', $article->title));
            return $this->redirect(['action' => 'index']);
        }
    }

    // Add API functionality here:
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    public function findAll(): \Cake\Http\Response
    {
        $this->autoRender = false;

        $articles = $this->Articles->find('all')->all();
        $this->viewBuilder()->setOption('serialize', ['articles']);

        $result = json_encode(array($articles));
        return $this->response->withType("application/json")->withStringBody($result);
    }

    public function findById($id): \Cake\Http\Response
    {
        $this->autoRender = false;

        $article = $this->Articles->get($id);
        $this->viewBuilder()->setOption('serialize', ['article']);

        $result = json_encode(array($article));
        return $this->response->withType("application/json")->withStringBody($result);
    }

    public function create()
    {
        $validator = new Validator();
        $validator->requirePresence('title')
            ->notEmptyString('title', 'A title is required')
            ->add('title', [
                'length' => [
                    'rule' => ['minLength', 10],
                    'message' => 'Titles need to be at least 10 characters long',
                ]
            ])
            ->requirePresence('body')
            ->notEmptyString('body', 'Text for the body is required');

        $errors = $validator->validate($this->request->getData());
        if (!empty($errors)) {
            $result = json_encode($errors);
            return $this->response->withType("application/json")->withStringBody($result);
        }

        $article = $this->Articles->newEmptyEntity();

        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            $article->user_id = 1;

            if ($this->Articles->save($article)) {
                return $this->response->withType("application/json")->withStringBody('Success');
            }
            return $this->response->withType("application/json")->withStringBody('Failure');
        }
    }

    public function update($id)
    {
        $this->autoRender = false;

        $article = $this->Articles->get($id);

        if ($this->request->is(['put'])) {
            $this->Articles->patchEntity($article, $this->request->getData());
            if ($this->Articles->save($article)) {
                return $this->response->withType("application/json")->withStringBody('Success');
            }
            return $this->response->withType("application/json")->withStringBody('Failure');
        }
    }

    public function remove($id)
    {
        $article = $this->Articles->get($id);
        $message = 'Deleted';
        if (!$this->Articles->delete($article)) {
            $message = 'Error';
        }
        return $this->response->withType("application/json")->withStringBody($message);
    }
}
