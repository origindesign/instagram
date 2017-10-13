<?php

namespace Drupal\instagram;

use Drupal\Core\Database\Connection;


class InstagramStorage {



    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $database;


    /**
     * InstagramStorage constructor.
     * @param Connection $database
     */
    public function __construct(Connection $database) {
        $this->database = $database;
    }


    /**
     * @param array $images
     * @param bool $truncate
     * @return bool
     */
    public function storeImages($images,$truncate = false){

        if($truncate){
            $query = $this->database->truncate('instagram_images');
            $query->execute();
        }
        
        foreach($images as $key => $image){
            
            $query = $this->database->merge('instagram_images');
            $query->key(array(
                'iid' => $key+1
            ));
            $query->fields(array(
                'id' => $image['id'],
                'link' => $image['link'],
                'created_time' => $image['created_time'],
                'type' => $image['type'],
                'thumbnail' => $image['thumbnail'],
                'standard_resolution' => $image['standard_resolution'],
                'low_resolution' => $image['low_resolution'],
                'likes' => $image['likes'],
                'caption' => $image['caption'],
                'tags' => implode(', ',$image['tags'])
            ));

            if ( !$query->execute() ){
                return false;
            }

        }

        return true;

    }


    /**
     * Get images from DB
     * @return array
     */
    public function getImages(){

        $query = $this->database->select('instagram_images','instagram');
        $query->fields('instagram');
        $result = $query->execute()->fetchAll();

        $data = array();

        foreach($result as $record) {
            $data[] = array(
                'id' => $record->id,
                'link' => $record->link,
                'created_time' => $record->created_time,
                'type' => $record->type,
                'thumbnail' => $record->thumbnail,
                'standard_resolution' => $record->standard_resolution,
                'low_resolution' => $record->low_resolution,
                'likes' => $record->likes,
                'caption' => $record->caption,
                'tags' => $record->tags,
            );
        }

        return $data;

    }


}