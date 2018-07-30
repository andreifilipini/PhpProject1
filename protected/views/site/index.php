<div id="logo" style="text-align: center;">
    <img src="http://www.zoomdigital.com.br/img/2012/06/twitter-novo-logo.jpg" width="20%"/>
</div>
<form action="" method="post">
    <div id="app" class="row">
        <div id="lastSearch" class="col-md-6">
            <hr>
            <div>
                {{ title }}
            </div>
            <div v-for="(item, index) in items">
                {{ item.history }}
            </div>
        </div>

        <div id="trendingTopics" class="col-md-6">
            <hr>
            <div>
                {{ title }}
            </div>
            <div v-for="(item, index) in items">
                {{ item.name }}
            </div>
        </div>
        <hr>     
        <input type="text" v-model="message" placeholder="Search it" name="keyword" class="col-md-12">

        <div id="search" class="col-md-12">
            <div class="row" v-for="(item, index) in items">
                <div class="col-md-2" style="text-align: center;">
                    <img v-bind:src="item.imageLink" class="imgCircle"/>
                </div>
                <div class="col-md-10">
                    <span class="userName">
                        {{ item.name }}
                    </span>
                    <br>
                    <span>
                        {{ item.text }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
$searchJson = '[]';
$historyJson = '[]';

$oauth_token = "962680258528010242-LeTqUiakHgPIDUiDUyll6Iewl51IcHO";
$oauth_token_secret = "OnnJq2ylnlhTO71RFbmBmXVwUYHHQXCpk6hD9jm1qdn0y";
$twitter = Yii::app()->twitter->getTwitterTokened($oauth_token, $oauth_token_secret);

if (isset($_POST['keyword'])) {
    $tweets = $twitter->get('https://api.twitter.com/1.1/search/tweets.json?q=' . $_POST['keyword'] . '&result_type=popular')->statuses;

    $searchJson = '[';
    $first = true;
    foreach ($tweets as $tweet) {
        if (!$first) {
            $searchJson .= ',';
        }
        $first = false;
        $searchJson .= '{imageLink: \'' . $tweet->user->profile_image_url . '\',';
        $searchJson .= 'name: \'' . $tweet->user->name . '\',';
        $searchJson .= 'text: \'' . str_replace("'", "", $tweet->text) . '\'}';
    }
    $searchJson .= ']';
    $searchJson = preg_replace("/\r|\n/", "", $searchJson);

    if (!file_exists('history.txt')) {
        file_put_contents('history.txt', '');
    }
    $fileContent = file_get_contents('history.txt');

    $list = array();
    if (trim($fileContent) != "") {
        $list = explode(";", $fileContent);
    }

    array_push($list, $_POST['keyword']);
    $list = array_reverse($list);
    
    $max = 5;
    $historyJson = '[';
    $first = true;

    foreach ($list as $history) {
        if ($max > 0) {
            if (!$first) {
                $historyJson .= ',';
            }
            $first = false;
            $historyJson .= '{history: \'' . $history . '\'}';
        }
        $max--;
    }
    $historyJson .= ']';


    $fileContent = '';
    $list = array_reverse($list);
    foreach ($list as $history) {
        $fileContent .= $history . ';';
    }
    $fileContent = substr($fileContent, 0, -1);
    file_put_contents('history.txt', $fileContent);
}

$trends = $twitter->get('https://api.twitter.com/1.1/trends/place.json?id=1');

$trendJson = '[';
$i = 0;
foreach ($trends as $t) {
    $aux = $t->trends;
    foreach ($aux as $tr) {
        if ($i < 5) {
            if ($i > 0) {
                $trendJson .= ',';
            }
            $trendJson .= '{name: \'' . $tr->name . '\'}';
        }
        $i++;
    }
}
$trendJson .= ']';
$trendJson = preg_replace("/\r|\n/", "", $trendJson);
?>
<script>

    var lastSearch = new Vue({
        el: '#lastSearch',
        data: {
            title: 'Recent Searches',
            items: <?php echo $historyJson ?>
        }
    });

    var trendingTopics = new Vue({
        el: '#trendingTopics',
        data: {
            title: 'Trending Now',
            items: <?php echo $trendJson ?>
        }
    });

    var search = new Vue({
        el: '#search',
        data: {
            items: <?php echo $searchJson ?>
        }
    });
</script>
