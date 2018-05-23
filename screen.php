<html>
<head>
  <link rel="stylesheet" type="text/css" href="screen.css">
</head>
<body>
  <h1>Kahvila avoinna</h1>
<?php

include_once('simple_html_dom.php');

$BASE_URL = "http://www.hps.fi/kentat-ja-tilat/hps-kahvila/kahvilan-aukioloajat/";
$MAGIC_PREFIX = '1370357';

$time_now = time();
$time_nextweek = $time_now+(7*24*60*60);

$week_now = date('W', $time_now);
$range_now = date('Y-m', $time_now);

$week_nextweek = date('W', $time_nextweek);
$range_nextweek = date('Y-m', $time_nextweek);

// Make query for this week
$query_array = Array(
  'E'.$MAGIC_PREFIX.'WEEK' => $week_now,
  'E'.$MAGIC_PREFIX.'rangeMonth' => $range_now,
  'x'.$MAGIC_PREFIX => ''
);

$open_now = get_one_calendar($query_array);

// Make query for next week
$query_array = Array(
  'E'.$MAGIC_PREFIX.'WEEK' => $week_nextweek,
  'E'.$MAGIC_PREFIX.'rangeMonth' => $range_nextweek,
  'x'.$MAGIC_PREFIX => ''
);

$open_nextweek = get_one_calendar($query_array);

echo(get_calendar_html(array_merge($open_now, $open_nextweek)));

exit();

function get_calendar_html ($event_arr) {
  $today_human = date('d.n.Y');

  $html = '<table>';

  foreach ($event_arr as $this_event) {
    $this_date = "";
    $this_rest = $this_event;

    if (preg_match('/(.+) \((.+) klo/', $this_event, $matches)) {
      $this_date = $matches[2];
      $this_rest = $matches[1];
    }

    if (preg_match('/'.$today_human.'/', $this_event)) {
      $html .= '<tr><td class="today">Tänään: '.$this_rest.'</td></tr>';
    }
    else {
      $html .= '<tr><td>'.$this_date.': '.$this_rest.'</td></tr>';
    }
  }

  $html .= '</table>';

  return $html;
}

function get_one_calendar($query_array) {
  global $BASE_URL;

  $url = $BASE_URL.'?'.http_build_query($query_array);

  $result = file_get_contents($url);

  // Catch calendar table
  $html = str_get_html($result);

  $result_arr = Array();
  foreach ($html->find("table.cc") as $this_table) {
    foreach ($this_table->find("a") as $this_cell) {
      if (preg_match('/avoinna/i', $this_cell->plaintext) or preg_match('/klo/i', $this_cell->plaintext)) {
        array_push($result_arr, $this_cell->plaintext);
      }
    }
  }

  return $result_arr;
}

?>
</body>
</html>
