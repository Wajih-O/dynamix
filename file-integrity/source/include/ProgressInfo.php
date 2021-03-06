<?PHP
/* Copyright 2015-2016, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */
?>
<?
function status($cmd,$name,$file) {
  global $list;
  if (!$file) return "close blue-text";
  return ($list && strpos($list[$name],$cmd)!==false) ? "check green-text" : "circle-o orange-text";
}

if ($_POST['disk']>0) {
  $tmp = "/var/tmp/disk{$_POST['disk']}.tmp";
  $end = "$tmp.end";
  if (file_exists($tmp)) {
    echo file_get_contents($tmp);
  } else {
    echo file_exists($end) ? file_get_contents($end) : "100%#<span class='red-text red-button'>Error</span>Operation aborted#";
    @unlink($end);
  }
} else {
  $ctrl = "/var/tmp/ctrl.tmp";
  if (!file_exists($ctrl) || (time()-filemtime($ctrl)>=$_POST['time'])) {
    exec("/etc/cron.daily/exportrotate -q 1>/dev/null 2>&1 &");
    touch($ctrl);
  }
  $path = "/boot/config/plugins/dynamix.file.integrity";
  $list = @parse_ini_file("$path/disks.ini");
  $disks = parse_ini_file("state/disks.ini",true);
  $row1 = $row2 = [];
  foreach ($disks as $disk) {
    if ($disk['type']=='Data' && strpos($disk['status'],'_NP')===false) {
      $name = $disk['name'];
      $row1[] = "<td style='text-align:center'><i class='fa fa-".status('build',$name,true)."'></i></td>";
      $row2[] = "<td style='text-align:center'><i class='fa fa-".status('export',$name,file_exists("$path/export/$name.export.hash"))."'></i></td>";
    }
  }
  echo "<tr><td style='font-style:italic'>Build up-to-date</td>";
  echo implode('',$row1);
  echo "</tr><tr id='export-status'><td style='font-style:italic'>Export up-to-date</td>";
  echo implode('',$row2);
  echo "</tr>";
}
?>
