<?php
/** @var array $routeInfo */
/** @var string $appname */
/** @var \Tiny\Interfaces\RequestInterface $request */
/** @var array $tpl_vars */

/** @var string $title */
/** @var array $list */

?>

<p>app:<?= $appname ?>, route:<?= $request->getCurrentRoute() ?><p>
<h1 id="widget-test"><?= $title ?></h1>
<?php foreach ($list as $item) { ?>
    <li><?= $item ?></li>
<?php } ?>

<?= \Tiny\Plugin\Fis::styleStart($response) ?>
<style type="text/css">
    #widget-test {
        color: #2fd058;
    }
</style>
<?= \Tiny\Plugin\Fis::styleEnd($response) ?>

<?= \Tiny\Plugin\Fis::scriptStart($response) ?>
<script type="text/javascript">
    $(function () {
        var xxx = require('static/api/ApiHub');
        xxx.hello({name: '   ' + $('#widget-test').text() + '     '}, function (res) {
            $('#widget-test').text( res.info );
        });
    });

    require.async('static/api/GraphQLApi.js',function(obj){
        console.log(obj);
    });
</script><?= \Tiny\Plugin\Fis::scriptEnd($response) ?>



