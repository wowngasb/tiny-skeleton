<?php
/** @var array $routeInfo */
/** @var string $appname */
/** @var string $webname */
/** @var \Tiny\Request $request */
/** @var array $tpl_vars */

/** @var int $a */
/** @var int $b */
/** @var int $sum */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?= htmlspecialchars($webname) ?>-测试</title>

    <?= \Tiny\Plugin\Fis::framework('static/mod.js') ?>
    <!-- 标记css输出位置 -->
    <?= \Tiny\Plugin\Fis::placeholder('css') ?>

</head>
<body>
<p>Test App:<?= $appname ?>, web:<?= htmlspecialchars($webname) ?>, route:<?= $request->getCurrentRoute() ?><p>
<h1>a = <?= $a ?></h1>
<h1>b = <?= $b ?></h1>
<h1>sum = <?= $sum ?></h1>
<h1 id="api-test"></h1>
<?= \Tiny\Plugin\Fis::import('view/index/index/test2.css') ?>

<?= \Tiny\Plugin\Fis::styleStart() ?>
<style type="text/css">
    #api-test {
        color: #00A7D0;
    }
</style>
<?= \Tiny\Plugin\Fis::styleEnd() ?>
<hr/>
<?= \Tiny\View\ViewFis::widget('widget/index/test-widget.php', ['list' => ['item 1', 'item 2', 'item 3'], 'title' => 'TEST']) ?>
</body>
<?= \Tiny\Plugin\Fis::import('static/jquery/jquery-1.7.2.min.js', false) ?>

<?= \Tiny\Plugin\Fis::scriptStart() ?>
<script type="text/javascript">
    var ApiHub = require('static/api/ApiHub');
    $(function () {
        ApiHub.hello({}, function (res) {
            $('#api-test').text(res.info);
        });
        console.log('ApiHub.hello_args', ApiHub.hello_args);
    });
    setTimeout(function(){
        ApiHub.testError({id:'abc'});
    }, 2000);
</script><?= \Tiny\Plugin\Fis::scriptEnd(0) ?>

<!-- js输出位置，放在底部加快页面解析 -->

<?= \Tiny\Plugin\Fis::placeholder('js') ?>

</html>
