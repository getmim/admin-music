<?php
/**
 * AlbumController
 * @package admin-music
 * @version 0.0.1
 */

namespace AdminMusic\Controller;

use LibFormatter\Library\Formatter;
use LibForm\Library\Form;
use LibPagination\Library\Paginator;
use Music\Model\{
    Music,
    MusicAlbum as MAlbum
};
use AdminSiteMeta\Library\Meta;

class AlbumController extends \Admin\Controller
{
    private function getParams(string $title): array{
        return [
            '_meta' => [
                'title' => $title,
                'menus' => ['music', 'album']
            ],
            'subtitle' => $title,
            'pages' => null
        ];
    }

    public function editAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_music_album)
            return $this->show404();

        $album = (object)[];

        $id = $this->req->param->id;
        if($id){
            $album = MAlbum::getOne(['id'=>$id]);
            if(!$album)
                return $this->show404();
            Meta::parse($album, 'meta');
            $params = $this->getParams('Edit Music Album');
        }else{
            $params = $this->getParams('Create New Music Album');
        }

        $form              = new Form('admin-music-album.edit');
        $params['form']    = $form;
        $params['schemas'] = ['MusicAlbum'=>'MusicAlbum'];

        if(!($valid = $form->validate($album)) || !$form->csrfTest('noob'))
            return $this->resp('music/album/edit', $params);

        Meta::combine($valid, 'meta');

        if($id){
            if(!MAlbum::set((array)$valid, ['id'=>$id]))
                deb(MAlbum::lastError());
        }else{
            $valid->user = $this->user->id;
            if(!MAlbum::create((array)$valid))
                deb(MAlbum::lastError());
        }

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => $id ? 2 : 1,
            'type'   => 'music-album',
            'original' => $album,
            'changes'  => $valid
        ]);

        $next = $this->router->to('adminMusicAlbum');
        $this->res->redirect($next);
    }

    public function indexAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_music_album)
            return $this->show404();

        $cond = $pcond = [];
        if($q = $this->req->getQuery('q'))
            $pcond['q'] = $cond['q'] = $q;

        list($page, $rpp) = $this->req->getPager(25, 50);

        $albums = MAlbum::get($cond, $rpp, $page, ['name'=>true]) ?? [];
        if($albums)
            $albums = Formatter::formatMany('music-album', $albums, ['user']);

        $params             = $this->getParams('Music Album');
        $params['albums']   = $albums;
        $params['form']     = new Form('admin-music-album.index');

        $params['form']->validate( (object)$this->req->get() );

        // pagination
        $params['total'] = $total = MAlbum::count($cond);
        if($total > $rpp){
            $params['pages'] = new Paginator(
                $this->router->to('adminMusicAlbum'),
                $total,
                $page,
                $rpp,
                10,
                $pcond
            );
        }

        $this->resp('music/album/index', $params);
    }

    public function removeAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_music_album)
            return $this->show404();

        $id     = $this->req->param->id;
        $album  = MAlbum::getOne(['id'=>$id]);
        $next   = $this->router->to('adminMusicAlbum');
        $form   = new Form('admin-music-album.index');

        if(!$form->csrfTest('noob'))
            return $this->res->redirect($next);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => 3,
            'type'   => 'music-album',
            'original' => $album,
            'changes'  => null
        ]);

        MAlbum::remove(['id'=>$id]);
        Music::set(['album'=>0], ['album'=>$id]);

        $this->res->redirect($next);
    }
}