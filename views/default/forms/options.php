<?php
/**
 * Advanced search plugin settings
 */
elgg_require_js("elgg/more_solr_style");

$search = elgg_echo('options:search');
$search_bar = elgg_view('input/text', array(
    'name' => 'search',
    'value' => $_GET['search'] ? $_GET['search'] : '',
    'class' => 'elgg-input-thin requiredFields',
    'placeholder' => elgg_echo('options:search:placeholder'),
));
// render placeholder separately so it will double-encode if needed
$placeholder = htmlspecialchars(elgg_echo('search'), ENT_QUOTES, 'UTF-8');

$synonym = elgg_echo('options:synonym');
$syn_bar = elgg_view('input/select', array(
    'name' => 'synonym',
    'value' => $_GET['synonym'] ? $_GET['synonym'] : '',
    'options_values' => array(
        'yes' => elgg_echo('option:yes'),
        'no' => elgg_echo('option:no'),
    ),
));

$pizza  = elgg_get_plugin_setting('cate_en', 'more_solr');
if($pizza != 'no'){
    $pizza  = elgg_get_plugin_setting('category_groups', 'more_solr');
    $pizza ? $groupListValue = explode("[",$pizza) : $groupListValue = elgg_echo('option:all');

    $groupnamelist = ['all', 'group', 'user'];
    foreach ($groupListValue as $value) {
        $value = explode(",", $value);
        if ($value[0]) {
            $groupnamelist[] .= elgg_echo('option => '.$value[0]);
        }
    }
} else {
    $pizza  = elgg_get_plugin_setting('cat_list', 'more_solr');
    $pieces = explode(",", $pizza);
    foreach($pieces as $piece){
        if ($piece) {
            $groupnamelist["$piece"] .= elgg_echo($piece);
        }
    }
}
$categoriesGroups = $groupnamelist;
$category = elgg_echo('options:category');
$cat_bar = elgg_view('input/select', array(
    'name' => 'category',
    'value' => $_GET['category'] ? $_GET['category'] : '',
    'options_values' => $categoriesGroups,
));

$arr = [];
$pizza  = elgg_get_plugin_setting('sort_list', 'more_solr');
$default  = elgg_get_plugin_setting('sort_def', 'more_solr');
$pieces = explode(",", $pizza);
foreach($pieces as $piece){
    print_r($types['object'][$piece]);
    $arr["$piece"] = elgg_echo('option:'.$piece);
}
$sort = elgg_echo('options:sort');
$sort_bar = elgg_view('input/select', array(
    'name' => 'sort',
    'value' => $_GET['sort'] ? $_GET['sort'] : $default,
    'options_values' => $arr,
));

$tags = elgg_echo('options:tags');
$tags_bar = elgg_view('input/text', array(
    'name' => 'tags',
    'value' => $_GET['tags'] ? $_GET['tags'] : '',
    'class' => 'elgg-input-thin',
    'placeholder' => elgg_echo('options:tags:placeholder'),
));

$user = elgg_echo('options:user');
$user_bar = elgg_view('input/text', array(
    'name' => 'user',
    'id' => 'userAuto',
    'value' => $_GET['user'] ? $_GET['user'] : '',
    'class' => 'elgg-input-thin',
    'placeholder' => elgg_echo('options:user:placeholder'),
));

$date = elgg_echo('options:date:from');
$date_bar = elgg_view('input/date', array(
    'name' => 'date',
    'id' => 'date',
    'value' => $_GET['date'] ? $_GET['date'] : '',
    'class' => 'elgg-input-thin',
    'placeholder' => elgg_echo('options:date:from:placeholder'),
));

$dateTo = elgg_echo('options:date:to');
$date_barTo = elgg_view('input/date', array(
    'name' => 'dateTo',
    'id' => 'dateTo',
    'value' => $_GET['dateTo'] ? $_GET['dateTo'] : '',
    'class' => 'elgg-input-thin',
    'placeholder' => elgg_echo('options:date:to:placeholder'),
));

$pizza  = elgg_get_plugin_setting('res_am', 'more_solr');
$carr = [];
$arr = [$pizza - 30, $pizza - 10, $pizza, $pizza + 10, $pizza + 30];
$farr = array_filter($arr, function ($x) { return $x > 0; });
foreach($farr as $f){
    print_r($types['object'][$f]);
    $carr["$f"] = elgg_echo(''.$f);
}
$results = elgg_echo('options:results');
$results_bar = elgg_view('input/select', array(
    'name' => 'results',
    'value' => $_GET['results'] ? $_GET['results'] : '',
    'options_values' => $carr,
));

/*
 * Elgg way
 */

$userArray = [];
$userResults = elgg_get_entities(array(
        'types' => 'user',
        'limit' => 0,)
);

//  Get admins
$admin_guids = elgg_get_admins(array(
    'limit' => 0,
    'callback' => function ($row) { return $row->guid; }, // no overhead of entity creation
));

foreach($userResults as $v){
    $president  = elgg_get_plugin_setting('usAd_en', 'more_solr');
    if($president == 'no'){
        if(!in_array($v->guid,$admin_guids))
        {
            array_push($userArray, $v->name.":".$v->guid);
        }
    }
    else {
        array_push($userArray, $v->name.":".$v->guid);
    }
}

/*
 * Solr way
 */


$userArray = [];
//  Start of retrieving results
//
$client = elgg_solr_get_client();

//  Get a select query instance
$query = $client->createQuery($client::QUERY_SELECT);
$query->setStart(0)->setRows(8000);
$query->createFilterQuery('type')->setQuery('type:user');
$query->setFields(array('id','name'));
// This executes the query and returns the result
$userResults = $client->select($query);

foreach($userResults as $v){
    array_push($userArray, $v->name.":".$v->id);
}



$json = json_encode(utf8ize($userArray), JSON_UNESCAPED_UNICODE);
$kappa_bar = elgg_view('input/text', array(
    'name' => 'getUsers',
    'class' => 'hidden',
    'id' => 'hiddenUsers',
    'value' => $json,
));

$submit = elgg_view('input/submit', array(
    'value' => elgg_echo('search:go')
));
$optionsTitle = elgg_echo('options:title');
$settings = "
<div class='popup-body'>
        <h1>$optionsTitle</h1>
    <table>
     <thead>
      <tr>
         <th></th>
         <th></th>
      </tr>
     </thead>
      <tr>
        <td><label>$search</label></td>
        <td>$search_bar</td>
      </tr>";
$setting = elgg_get_plugin_setting('syn_en', 'more_solr');
if($setting != 'no'){
    $settings .= "
      <tr>
        <td><label>$synonym</label></td>
        <td>$syn_bar</td>
      </tr>";
}
$setting = elgg_get_plugin_setting('cat_en', 'more_solr');
if($setting != 'no'){
    $settings .= "
      <tr>
        <td><label>$category</label></td>
        <td>$cat_bar</td>
      </tr>";
}
$setting = elgg_get_plugin_setting('sort_en', 'more_solr');
if($setting != 'no'){
    $settings .= "
      <tr>
        <td><label>$sort</label></td>
        <td>$sort_bar</td>
      </tr>";
}
$setting = elgg_get_plugin_setting('tags_en', 'more_solr');
if($setting != 'no'){
    $settings .= "
      <tr>
        <td><label>$tags</label></td>
        <td>$tags_bar</td>
      </tr>";
}
$setting = elgg_get_plugin_setting('user_en', 'more_solr');
if($setting != 'no'){
    $settings .= "
      <tr>
        <td><label>$user</label></td>
        <td>$user_bar</td>
      </tr>";
}
$setting = elgg_get_plugin_setting('date_en', 'more_solr');
if($setting != 'no') {
    $settings .= "
      <tr>
        <td><label>$date</label></td>
        <td>$date_bar</td>
      </tr>
      <tr>
        <td><label>$dateTo</label></td>
        <td>$date_barTo</td>
      </tr>";
}
$setting = elgg_get_plugin_setting('res_en', 'more_solr');
if($setting != 'no') {
    $settings .= "
      <tr>
        <td><label>$results</label></td>
        <td>$results_bar</td>
        $kappa_bar
      </tr>";
}
    $settings .= "
    </table>
    $submit
</div>";

echo $settings;

function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}
