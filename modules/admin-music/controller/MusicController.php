<?php
/**
 * MusicController
 * @package admin-music
 * @version 0.0.1
 */

namespace AdminMusic\Controller;

use LibFormatter\Library\Formatter;
use LibForm\Library\Form;
use LibForm\Library\Combiner;
use LibPagination\Library\Paginator;
use Music\Model\{
    Music,
    MusicAlbum as MAlbum
};

class MusicController extends \Admin\Controller
{
    private function getParams(string $title): array{
        return [
            '_meta' => [
                'title' => $title,
                'menus' => ['music', 'all-music']
            ],
            'subtitle' => $title,
            'pages' => null
        ];
    }

    public function editAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_music)
            return $this->show404();

        $music = (object)[];

        $id = $this->req->param->id;
        if($id){
            $music = Music::getOne(['id'=>$id]);
            if(!$music)
                return $this->show404();
            $params = $this->getParams('Edit Music');
        }else{
            $params = $this->getParams('Create New Music');
        }

        $form           = new Form('admin.music.edit');
        $params['form'] = $form;

        $c_opts = [
            'meta'  => [null, null, 'json'],
            'album' => [null, null, 'format', 'active', 'name']
        ];

        $combiner = new Combiner($id, $c_opts, 'music');
        $music    = $combiner->prepare($music);

        $params['opts'] = $combiner->getOptions();

        if(!($valid = $form->validate($music)) || !$form->csrfTest('noob'))
            return $this->resp('music/edit', $params);

        $valid = $combiner->finalize($valid);

        if($id){
            if(!Music::set((array)$valid, ['id'=>$id]))
                deb(Music::lastError());
        }else{
            $valid->user = $this->user->id;
            if(!($id = Music::create((array)$valid)))
                deb(Music::lastError());
        }

        $combiner->save($id, $this->user->id);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => $id ? 2 : 1,
            'type'   => 'music',
            'original' => $music,
            'changes'  => $valid
        ]);

        $next = $this->router->to('adminMusic');
        $this->res->redirect($next);
    }

    public function indexAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_music)
            return $this->show404();

        $cond = $pcond = [];
        if($q = $this->req->getQuery('q'))
            $pcond['q'] = $cond['q'] = $q;

        if($album = $this->req->getQuery('album')){
            if(preg_match('!^id-([0-9]+)$!', $album, $match)){
                $cond['album.id'] = $match[1];
            }else{
                $pcond['album'] = $album;
                $cond['album.name'] = ['__like', $album];
            }
        }

        list($page, $rpp) = $this->req->getPager(25, 50);

        $musics = Music::get($cond, $rpp, $page, ['title'=>true]) ?? [];
        if($musics)
            $musics = Formatter::formatMany('music', $musics, ['user','album']);

        $params             = $this->getParams('Music');
        $params['musics']   = $musics;
        $params['form']     = new Form('admin.music.index');

        $params['form']->validate( (object)$this->req->get() );

        // pagination
        $params['total'] = $total = Music::count($cond);
        if($total > $rpp){
            $params['pages'] = new Paginator(
                $this->router->to('adminMusic'),
                $total,
                $page,
                $rpp,
                10,
                $pcond
            );
        }

        $this->resp('music/index', $params);
    }

    public function removeAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_music)
            return $this->show404();

        $id     = $this->req->param->id;
        $music  = Music::getOne(['id'=>$id]);
        $next   = $this->router->to('adminMusic');
        $form   = new Form('admin-music.index');

        if(!$form->csrfTest('noob'))
            return $this->res->redirect($next);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => 3,
            'type'   => 'music',
            'original' => $music,
            'changes'  => null
        ]);

        Music::remove(['id'=>$id]);

        $this->res->redirect($next);
    }
}