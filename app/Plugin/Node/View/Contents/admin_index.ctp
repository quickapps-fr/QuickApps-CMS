<?php
$tSettings = array(
    'columns' => array(
        '<input type="checkbox" onclick="QuickApps.checkAll(this);">' => array(
            'value' => '<input type="checkbox" name="data[Items][id][]" value="{Node.id}">',
            'thOptions' => array('align' => 'center'),
            'tdOptions' => array('width' => '25', 'align' => 'center')
        ),
        __t('Title') => array(
            'value' => '{Node.title} 
                {php} return ({Node.sticky}) ? \'<img src="{url}/node/img/sticky.png{/url}" title="' . __t('Sticky at top') . '" />\' : ""; {/php} 
                {php} return ({Node.promote}) ? \'<img src="{url}/node/img/promote.png{/url}" title="' . __t('Promoted in front page') .'" />\' : ""; {/php}
                {php} return (trim("{Node.cache}") != "") ? \'<img src="{url}/node/img/cache.png{/url}" title="' . __t('Cache activated') .': {Node.cache}" />\' : ""; {/php}',
            'sort' => 'Node.title',
            'tdOptions' => array('width' => '40%', 'align' => 'left')
        ),
        __t('Type') => array(
            'value' => '{php} return ("{NodeType.name}" != "") ? "{NodeType.name}" : "---"; {/php}',
            'sort'    => 'NodeType.id'
        ),
        __t('Author') => array(
            'value' => '{CreatedBy.name}',
            'sort'    => 'NodeType.id'
        ),
        __t('Status') => array(
            'value' => '{php} return ( {Node.status} == 0 ? "' . __t('not published') . '" : "' . __t('published') . '" ); {/php}',
            'sort'    => 'Node.status'
        ),
        __t('Updated') => array(
            'value' => '{php} return $this->_View->Time->format("' . __t('Y/m/d - H:i') . '", {Node.modified}, null, "' . $this->Session->read('Auth.User.timezone') . '"); {/php} {php} return ( {Node.modified} != {Node.created} ? "<span style=\\"color:red;\\">' . __t('updated') . '</span>" : "" ); {/php}',
            'sort' => 'Node.modified'
        ),
        __t('Language') => array(
            'value' => '{php} return ( "{Node.language}" == "" ? "' . __t('-- Any --') . '" : "{Node.language}" ); {/php}',
            'sort' => 'Node.language'
        ),
        __t('Actions') => array(
            'value' => "
                <a href='{url}/admin/node/contents/edit/{Node.slug}{/url}'>" . __t('edit') . "</a> |
                <a href='{url}/admin/node/contents/delete/{Node.slug}{/url}' onclick=\"return confirm('" . __t('Delete selected content ?') . "');\">" . __t('delete') . "</a>",
            'thOptions' => array('align' => 'right'),
            'tdOptions' => array('align' => 'right')
        )
    ),
    'noItemsMessage' => __t('There are no nodes to display'),
    'paginate' => true,
    'headerPosition' => 'top',
    'tableOptions' => array('width' => '100%')    # table attributes
);
?>

<?php echo $this->Form->create(); ?>
    <!-- Filter -->
    <?php echo $this->Html->useTag('fieldsetstart', '<span id="toggle-filter_fieldset" style="cursor:pointer;">' . __t('Filter Options') . '</span>' ); ?>
        <div id="filter_fieldset" class="horizontalLayout" style="<?php echo isset($this->data['Node']['filter']) ? '' : 'display:none;'; ?>">
            <?php echo $this->Form->input('Node.filter.Node|title',
                    array(
                        'type' => 'text',
                        'label' => __t('Title')
                    )
                );
            ?>

            <?php echo $this->Form->input('Node.filter.Node|status',
                    array(
                        'type' => 'select',
                        'label' => __t('Status'),
                        'empty' => true,
                        'options' => array(
                            1 => __t('published'),
                            0 => __t('not published')
                        )
                    )
                );
            ?>

            <?php echo $this->Form->input('Node.filter.Node|promote',
                    array(
                        'type' => 'select',
                        'label' => __t('Front Page'),
                        'empty' => true,
                        'options' => array(
                            1 => __t('promoted to front page'),
                            0 => __t('not promoted to front page')
                        )
                    )
                );
            ?>

            <?php echo $this->Form->input('Node.filter.Node|sticky',
                    array(
                        'type' => 'select',
                        'label' => __t('Sticky'),
                        'empty' => true,
                        'options' => array(
                            1 => __t('sticky at top'),
                            0 => __t('not sticky at top')
                        )
                    )
                );
            ?>

            <?php echo $this->Form->input('Node.filter.NodeType|id',
                    array(
                        'type' => 'select',
                        'label' => __t('Type'),
                        'empty' => true,
                        'options' => $types
                    )
                );
            ?>

            <?php echo $this->Form->input(__t('Filter'), array('type' => 'submit', 'label' => false)); ?>
        </div>
    <?php echo $this->Html->useTag('fieldsetend'); ?>
<?php echo $this->Form->end(); ?>



<?php echo $this->Form->create(null, array('onsubmit' => 'return confirm("' . __t('Are you sure about this changes ?') . '");')); ?>
    <!-- Update -->
    <?php echo $this->Html->useTag('fieldsetstart', '<span id="toggle-update_fieldset" style="cursor:pointer;">' . __t('Update Options') . '</span>' ); ?>
        <div id="update_fieldset" class="horizontalLayout" style="<?php echo isset($this->data['Node']['update']) ? '' : 'display:none;'; ?>">
            <?php echo $this->Form->input('Node.update',
                    array(
                        'type' => 'select',
                        'label' => false,
                        'options' => array(
                            'publish' => __t('Publish selected content'),
                            'unpublish' => __t('Unpublish selected content'),
                            'promote' => __t('Promote selected content to front page'),
                            'demote' => __t('Demote selected content from front page'),
                            'sticky' => __t('Make selected content sticky'),
                            'unsticky' => __t('Make selected content not sticky'),
                            'delete' => __t('Delete selected content'),
                            'clear_cache' => __t('Clear cache')
                        )
                    )
                );
            ?>
            <?php echo $this->Form->input(__t('Update'), array('type' => 'submit', 'label' => false)); ?>
        </div>
    <?php echo $this->Html->useTag('fieldsetend'); ?>
    <!-- table results -->
    <?php echo $this->Html->table($results, $tSettings); ?>
    <!-- end: table results -->
<?php echo $this->Form->end(); ?>


<script type="text/javascript">

    $("#toggle-update_fieldset").click(function () {
        $("#update_fieldset").toggle('fast', 'linear');
    });

    $("#toggle-filter_fieldset").click(function () {
        $("#filter_fieldset").toggle('fast', 'linear');
    });
</script>