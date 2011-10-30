<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Configure::read('Variable.language.code'); ?>" version="XHTML+RDFa 1.0" dir="<?php echo Configure::read('Variable.language.direction'); ?>">
    <head>
        <title><?php echo $this->Layout->title(); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <?php echo $this->Layout->meta(); ?>
        <?php echo $this->Layout->stylesheets(); ?>
        <?php echo $this->Layout->javascripts(); ?>
        <?php echo $this->Layout->header(); ?>
    </head>

    <body>
        <div id="header-top">
            <div class="container">
                <?php if (Configure::read('Theme.settings.site_logo')): ?>
                    <a href="<?php echo $this->Html->url('/'); ?>" id="logo">
                        <?php echo $this->Html->image(Configure::read('Theme.settings.site_logo_url')); ?>
                    </a>
                <?php endif; ?>

                <?php if (!$this->Layout->emptyRegion('user-menu')): ?>
                <div id="user-menu">
                    <?php echo $this->Layout->blocks('user-menu'); ?>
                </div>
                <?php endif; ?>

                <?php if (!$this->Layout->emptyRegion('language-switcher')): ?>
                <div id="language-switcher">
                    <?php echo $this->Layout->blocks('language-switcher'); ?>
                </div>
                <?php endif; ?>

                <?php if (!$this->Layout->emptyRegion('search')): ?>
                <div id="search-block">
                    <?php echo $this->Layout->blocks('search'); ?>
                </div>
                <?php endif; ?>

             </div>
        </div>

        <div id="header-bottom">
            <div class="container">
                <?php echo $this->Layout->blocks('main-menu'); ?>
            </div>
        </div>

        <div id="page">
            <?php if (!$this->Layout->emptyRegion('slider')): ?>
            <div class="slider">
                <div class="container clearfix">
                    <?php echo $this->Layout->blocks('slider'); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($this->Layout->isFrontpage()): ?>
                <?php if (Configure::read('Theme.settings.site_slogan')): ?>
                <div id="quote">
                    <div id="quote-inner">
                        <div class="container">
                            <span id="quote-icon"></span>
                            <p id="slogan"><?php echo __t(Configure::read('Variable.site_slogan')); ?></p>
                        </div> <!-- end .container -->
                    </div> <!-- end #quote-inner -->
                </div> <!-- end #quote -->
                <?php endif; ?>

                <div class="container">
                    <div id="services">
                        <div class="container clearfix">
                            <div class="service">
                                <?php echo $this->Html->image('pen.png', array('class' => 'icon')); ?>
                                <?php echo $this->Layout->blocks('services-left'); ?>

                            </div> <!-- end .service -->

                            <div class="service">
                                <?php echo $this->Html->image('gear.png', array('class' => 'icon')); ?>
                                <?php echo $this->Layout->blocks('services-center'); ?>

                            </div> <!-- end .service -->

                            <div class="service last">
                                <?php echo $this->Html->image('bubble.png', array('class' => 'icon')); ?>
                                <?php echo $this->Layout->blocks('services-right'); ?>
                            </div> <!-- end .service -->
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="container">
                    <div id="help-blocks">
                        <?php echo $this->Layout->blocks('help'); ?>
                    </div>

                    <?php if ($sessionFlash = $this->Layout->sessionFlash()): ?>
                    <div id="sessionFlash">
                        <?php echo $sessionFlash; ?>
                    </div>
                    <?php endif; ?>

                    <div id="sidebar-left">
                        <?php echo $this->Layout->blocks('sidebar-left'); ?>&nbsp;
                    </div>

                    <div id="content" class="clearfix">
                        <?php echo $this->Layout->content(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div id="footer">
            <div class="container">
                <?php
                    if ($Layout['feed']) {
                        echo $this->Html->link(
                            $this->Html->image('feed.png'),
                            $Layout['feed'],
                            array(
                                'class' => 'rss-feed-icon',
                                'escape' => false
                            )
                        );
                    }
                ?>
                <?php echo $this->Layout->blocks('footer'); ?>
                &nbsp;
            </div>
        </div>

        <?php echo $this->Html->script('cufon-yui.js'); ?>
        <?php echo $this->Html->script('Colaborate-Thin_400.font.js'); ?>
        <script type="text/javascript">
            Cufon.replace('p#slogan', { fontFamily: 'Colaborate-Thin', fontSize: '30px' });
            Cufon.replace('h3', { fontFamily: 'Colaborate-Thin', fontSize: '30px' });
            Cufon.replace('.node-full h2.node-title', { fontFamily: 'Colaborate-Thin', fontSize: '40px' });
            Cufon.replace('.node-list h2.node-title', { fontFamily: 'Colaborate-Thin', fontSize: '30px' });
            Cufon.replace('#services h2', { fontFamily: 'Colaborate-Thin', fontSize: '20px' });
        </script>
        <?php echo $this->Layout->footer(); ?>
    </body>
</html>