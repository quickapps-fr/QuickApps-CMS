<style>
    hr.hooktags {
        width:100%;
        height:0px;
        text-align:left;
        border-top:0px;
        border-bottom:#ccc solid 1px;
        margin:20px 0;
    }
    p.code-block {
        display:block;
        background:#F0F0F0; border:1px solid #DADADA; color:#000; padding:10px; margin:25px 0;
    }
</style>

<h3>About</h3>
<p>
    The QuickApps module is integral to the site, and provides basic but extensible functionality for use by other modules and themes.
    Some integral elements of QuickApps are contained in and managed by the QuickApps module, including caching, enabling and disabling modules and themes and configuring fundamental site settings.
</p>

<h3>Uses</h3>
<dl>
    <dt>Hook tags</dt>
    <dd>
        <p>
            A hookTag is a QuickApps-specific code that lets you do nifty things with very little effort.
            hookTag can for example print current language code/name/nativeName or call especifics modules/themes functions.
            For example, block module has the 'block' hook which will print out the indicated block by id:
        </p>

        <p class="code-block">
            <code>[block id=1/]</code>
            <br/><br/>
            <em>This will render out the block with identifier equal to 1</em>
        </p>

        <p>
            You are able to define your own custom hookTags functions in: <em><?php echo APP . 'View' . DS . 'Helper' . DS; ?>CustomHooksHelper.php</em>
        </p>

        <p>
            Also modules and themes are able to define their own hookTags</em>
        </p>

        <hr class="hooktags" />

        Some useful built-in Hook Tags are:
        <p class="code-block">
            <code>[language.OPTION]</code>
            <br/><br/>
            Possible values of OPTION are: <em>code, name, native or direction.</em><br/>
            <b>code:</b> Returns language's <a href="http://www.sil.org/iso639-3/codes.asp" target="_blank">ISO 639-3 code</a> (eng, spa, fre, etc)<br/>
            <b>name:</b> Returns language's english name (English, Spanish, German, French, etc)<br/>
            <b>native:</b> Returns language's native name (English, Español, Deutsch, Fraçais, etc)<br/>
            <b>direction:</b> Returns direction that text is presented. <em>lft</em> (Left To Right) or <em>rtl</em> (Right to Left)
        </p>

        <p class="code-block">
            <code>[language]</code>
            <br/><br/>
            Shortcut for [language.code] which return current language code (eng, spa, etc).
        </p>

        <p class="code-block">
            <code>[t=domain@@text to translate by domain]</code>
            <br/><br/>
            Search for translation in specified domain, e.g: [t=system@@Help] will try to find translation for <em>Help</em> in <em>system</em> module translation table.
        </p>

        <p class="code-block">
            <code>[t=text to translate using default domain]</code>
            <br/><br/>
            Search for translation in (in the following order, if one fails then try the next method):<br/>
            - active runing module domain. <br/>
            - default domain ([t=default@@...]). <br/>
            - translatable entries table. (see `Locale` module)
        </p>

        <p class="code-block">
            <code>[url=/relative_url/image.jpg] or [url]relative url/image.jpg[/url]</code>
            <br/><br/>
            Return well formatted url. URL can be an relative url (/d/type-of-content/my-post) or external (http://www.domain.com/my-url).
        </p>

        <p class="code-block">
            <code>[date=FORMAT@@TIME_STAMP_OR_ENGLISH_DATE]</code>
            <br/><br/>
            Returns php result of <em>date(FORMAT, TIME_STAMP_OR_ENGLISH_DATE)</em>. <a href="http://www.php.net/manual/function.date.php" target="_blank">More info about date()</a><br/>
            It accepts both: numeric time stamp or english formatted date (Year-month-day Hours:Mins:Secs) as second parameter.
        </p>

        <p class="code-block">
            <code>[date=FORMAT]</code>
            <br/><br/>
            Returns php result of <em>date(FORMAT)</em>. <a href="http://www.php.net/manual/function.date.php" target="_blank">More info about date()</a>
        </p>
    </dd>

    <dt>Managing modules</dt>
    <dd>
        The QuickApps module allows users with the appropriate permissions to enable and disable modules on the 
        <a href="<?php echo $this->Html->url('/admin/system/modules'); ?>">Modules administration page</a>. 
        QuickApps CMS comes with a number of core modules, and each module provides a discrete set of features and may be enabled or disabled depending on the needs of the site. 
    </dd>

    <dt>Managing themes</dt>
    <dd>
        The QuickApps module allows users with the appropriate permissions to enable and disable themes on the <a href="<?php echo $this->Html->url('/admin/system/themes'); ?>">Appearance administration page</a>. 
        Themes determine the design and presentation of your site. 
        QuickApps CMS comes packaged with one core theme (Default).
    </dd>

    <dt>Configuring basic site settings</dt>
    <dd>
        The QuickApps module also handles basic configuration options for your site, 
        including Date and time settings, Site name and other information</a>.
    </dd>    
</dl>