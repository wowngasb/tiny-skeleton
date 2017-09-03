<?php
/** @var array $routeInfo */
/** @var string $appname */
/** @var string $webname */
/** @var \Tiny\Request $request */
/** @var array $tpl_vars */

/** @var string $title */
/** @var array $list */

?>

<p>app:<?= $appname ?>, web:<?= htmlspecialchars($webname) ?>, route:<?= $request->getCurrentRoute() ?><p>
<h1 id="widget-test"><?= $title ?></h1>
<?php foreach ($list as $item) { ?>
    <li><?= $item ?></li>
<?php } ?>

<?= \Tiny\Plugin\Fis::styleStart() ?>
<style type="text/css">
    #widget-test {
        color: #2fd058;
    }
</style>
<?= \Tiny\Plugin\Fis::styleEnd() ?>

<?= \Tiny\Plugin\Fis::scriptStart() ?>
<script type="text/javascript">
    $(function () {
        var xxx = require('static/api/ApiHub');
        xxx.hello({name: '   ' + $('#widget-test').text() + '     '}, function (res) {
            $('#widget-test').text( res.info );
        });
    });
</script><?= \Tiny\Plugin\Fis::scriptEnd() ?>



