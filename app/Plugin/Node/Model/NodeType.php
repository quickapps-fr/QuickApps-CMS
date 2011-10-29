<?php
/**
 * Node Type Model
 *
 * PHP version 5
 *
 * @package  QuickApps.Plugin.Node.Model
 * @version  1.0
 * @author   Christopher Castro <chris@quickapps.es>
 * @link     http://cms.quickapps.es
 */
class NodeType extends NodeAppModel {
    private $__tmp = null;
    public $name = 'NodeType';
    public $useTable = "node_types";
    public $primaryKey = 'id';
    public $actsAs = array('Sluggable' => array('overwrite' => false, 'slug' => 'id', 'label' => 'name', 'separator' => '_'));
    public $validate = array(
        'name' => array('required' => true, 'allowEmpty' => false, 'rule' => 'notEmpty', 'message' => 'Type name can not be empty'),
        'title_label' => array('required' => true, 'allowEmpty' => false, 'rule' => 'notEmpty', 'message' => 'Title field label can not be empty')
    );

    public function beforeDelete(){
        $this->tmpId = $this->id;
        return true;
    }

    public function beforeValidate() {
        if (isset($this->data['NodeType']['id']) && 
            isset($this->data['NodeType']['new_id']) && 
            $this->data['NodeType']['id'] != $this->data['NodeType']['new_id']
        ) {
            if ($this->field('module', array('NodeType.id' => $this->data['NodeType']['id'])) == 'Node') {
                $this->validate['new_id'] = array(
                    'required' => true, 'allowEmpty' => false, 'rule' => 'idAvailable', 'message' => 'Content type ID already in use'
                );

                $this->__tmp['old_id'] = $this->data['NodeType']['id'];
                $this->__tmp['new_id'] = $this->data['NodeType']['new_id'];
            }
        }    

        return true;
    }

    public function afterSave() {
        if (isset($this->__tmp['old_id'])) {
            # update ID
            $this->updateAll(
                array('NodeType.id' => "'{$this->__tmp['new_id']}'"),
                array('NodeType.id' => $this->__tmp['old_id'])
            );

            # update existing contents
            ClassRegistry::init('Node.Node')->updateAll(
                array('Node.node_type_id' => "'{$this->__tmp['new_id']}'"),
                array('Node.node_type_id' => $this->__tmp['old_id'])
            );

            #update related fields
            ClassRegistry::init('Field.Field')->updateAll(
                array('Field.belongsTo' => "'NodeType-{$this->__tmp['new_id']}'"),
                array('Field.belongsTo' => "NodeType-{$this->__tmp['old_id']}")
            );

            #try to correct URLs in existing menu links
            $MenuLink = ClassRegistry::init('Menu.MenuLink');

            $MenuLink->Behaviors->detach('Tree');

            $links = $MenuLink->find('all',
                array(
                    'conditions' => array(
                        'MenuLink.router_path LIKE' => "%/d/{$this->__tmp['old_id']}%"
                    ),
                    'fields' => array('id', 'router_path')
                )
            );

            foreach ($links as $link) {
                $link['MenuLink']['router_path'] = str_replace("/d/{$this->__tmp['old_id']}", "/d/{$this->__tmp['new_id']}" , $link['MenuLink']['router_path']);
                $MenuLink->save($link, false);
            }
        }

        return true;
    }

    public function afterDelete(){
        return ClassRegistry::init('Field.Field')->deleteAll(array('Field.belongsTo' => "NodeType-{$this->tmpId}"), true, true);
    }

    public function idAvailable($check) {
        $value = array_shift($check);

        if (!preg_match('/^[a-z0-9_]{3,}$/', $value)) {
            return false;
        }

        return $this->find('count',
            array(
                'conditions' => array(
                    'NodeType.id' => $value
                )
            )
        ) === 0;
    }
}