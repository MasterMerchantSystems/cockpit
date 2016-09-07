<?php

namespace Collections\Controller;

class RestApi extends \LimeExtra\Controller {

    public function getBySlug($collection=null) {
        return $this->get($collection, true);
    }

    public function get($collection=null, $bySlug=false) {

        if (!$collection) {
            return false;
        }

        $findBy = $bySlug ? 'slug':'name';

        $collection = $this->app->db->findOne("common/collections",  [$findBy=>$collection]);
        if (!$collection) {
            return false;
        }

        $entries = [];

        if ($collection) {
            $col     = "collection".$collection["_id"];
            $options = [];

            if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
            if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
            if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
            if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

            if (count($options)) {
                $options = json_decode(json_encode($options, JSON_NUMERIC_CHECK), true);
            }

            $entries = $this->app->db->find("collections/{$col}", $options);
            $entries_array = $entries->toArray();
            $modified_entries = array();
            foreach($entries_array as $entry){
                $modified_entry = array();
                foreach($entry as $key => $value){
                    if(stripos($key, 'image') !== false){
                        $path_to_thumbnail = cockpit('mediamanager:thumbnail', $value, 50, 50, ['mode' => 'best_fit']);
                        $modified_entry[$key . '_thumbnail'] = $path_to_thumbnail;
                    }
                    $modified_entry[$key] = $value;
                }
                $modified_entries[] = $modified_entry;
            }
        }

        return json_encode($modified_entries);
    }

}
