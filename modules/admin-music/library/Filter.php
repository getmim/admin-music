<?php
/**
 * Filter
 * @package admin-music
 * @version 0.0.1
 */

namespace AdminMusic\Library;

use Music\Model\MusicAlbum as MAlbum;

class Filter implements \Admin\Iface\ObjectFilter
{
    static function filter(array $cond): ?array{
        $cnd = [];
        if(isset($cond['q']) && $cond['q'])
            $cnd['q'] = (string)$cond['q'];
        $albums = MAlbum::get($cnd, 15, 1, ['name'=>true]);
        if(!$albums)
            return [];

        $result = [];
        foreach($albums as $album){
            $result[] = [
                'id'    => (int)$album->id,
                'label' => $album->name,
                'info'  => $album->name,
                'icon'  => NULL
            ];
        }

        return $result;
    }

    static function lastError(): ?string{
        return null;
    }
}