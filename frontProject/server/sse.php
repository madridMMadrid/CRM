<?php 
  header("Content-Type: text/event-stream\n\n");
  header("Cache-Control: no-store");

  $counter = rand(1, 10);
  while (1) {
    # Отправлять событие "ping" каждую секунду
    
    echo "event: ping\n";
    $curDate = date(DATE_ISO8601);
    echo 'data: {"time": "' . $curDate . '"}';
    echo "\n\n";
    
     $counter--;
    
    if (!$counter) {
      echo 'data: Время сообщения ' . $curDate . "\n\n";
      $counter = rand(1, 10);
    }
    
    ob_end_flush();
    flush();
    sleep(1);
  }
?>