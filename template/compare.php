<?php
$array_sql = array();
$arrayNewTable = array();
$i = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COMPALEX - database schema compare tool</title>
    <script src="./public/js/jquery.min.js"></script>
    <script src="./public/js/functional.js"></script>
    <style type="text/css" media="all">
        @import url("./public/css/style.css");
    </style>
</head>

<body>
<div class="modal-background" onclick="Data.hideTableData(); return false;">
    <div class="modal">
        <iframe src="" frameborder="0"></iframe>
    </div>
</div>

<div class="compare-database-block">
    <h1>Compalex<span style="color: red;">.net</span></h1>
    <h3>Database schema compare tool</h3>
    <table class="table">
        <tr class="panel">
            <td>
                <?php
                switch (DRIVER) {
                    case 'oci8':
                    case 'oci':
                    case 'mysql':
                        $buttons = array('tables', 'views', 'procedures', 'functions', 'indexes', 'triggers');
                        break;
                    case 'sqlserv':
                    case 'mssql':
                    case 'dblib':
                        $buttons = array('tables', 'views', 'procedures', 'functions', 'indexes');
                        break;
                    case 'pgsql':
                        $buttons = array('tables', 'views', 'functions', 'indexes');
                        break;
                }

                if (!isset($_REQUEST['action'])) $_REQUEST['action'] = 'tables';
                foreach ($buttons as $li) {
                    echo '<a href="index.php?action=' . $li . '"  ' . ($li == $_REQUEST['action'] ? 'class="active"' : '') . '>' . $li . '</a>&nbsp;';
                }
                ?>

            </td>
            <td class="sp">
                <a href="#" onclick="Data.showAll(this); return false;" class="active">all</a>
                <a href="#" onclick="Data.showDiff(this); return false;">changed</a>
                <a href="#" onclick="Data.showSql(this); return false;">SQL</a>

            </td>
        </tr>
    </table>
    <table class="table">
        <tr class="header">
            <td width="50%">
                <h2><?php echo DATABASE_NAME ?></h2>
                <h4 style="color: darkred; margin-top: 2px; "><?php echo DATABASE_DESCRIPTION ?></h4>
                <span><?php $spath = explode("@", FIRST_DSN);
                    echo end($spath); ?></span>
            </td>
            <td  width="50%">
                <h2><?php echo DATABASE_NAME_SECONDARY ?></h2>
                <h4 style="color: darkred; margin-top: 2px; "><?php echo DATABASE_DESCRIPTION_SECONDARY ?></h4>
                <span><?php $spath = explode("@", SECOND_DSN);
                    echo end($spath); ?></span>
            </td>
        </tr>
    <?php foreach ($tables as $tableName => $data) { ?>
        <tr class="data">
            <?php foreach (array('fArray', 'sArray') as $blockType) {
				$newTable = false;
				if($blockType == 'fArray' and  empty($data['sArray'])){
					$newTable = true;
					$arrayNewTable[$i][] = 'CREATE TABLE IF NOT EXISTS '.$tableName.' (';
				}
			?>
            <td class="type-<?php echo $_REQUEST['action']; ?>">
                <h3><?php echo $tableName; ?> <sup style="color: red;"><?php 
                if ($data != null && isset($data[$blockType]) && $data[$blockType] != null) {
                    echo count($data[$blockType]); 
                }?></sup></h3>
                <div class="table-additional-info">
                    <?php if(isset($additionalTableInfo[$tableName][$blockType])) {
                            foreach ($additionalTableInfo[$tableName][$blockType] as $paramKey => $paramValue) {
                                if(strpos($paramKey, 'ARRAY_KEY') === false) echo "<b>{$paramKey}</b>: {$paramValue}<br />";
                            }
                        }
                    ?>
                </div>
                <?php if ($data[$blockType]) { ?>
                    <ul>
                        <?php
							$primary	= false;
							$primaryKey	= '';
							foreach ($data[$blockType] as $fieldName => $tparam) { 
							$sql = '';
							if (isset($tparam['isNew']) && $tparam['isNew'] and  $blockType == 'fArray') {
								// print "<pre>";
								// // print_r($data[$blockType]);
								// print_r($tparam);
								// print "</pre>";
								if($newTable == true and $blockType == 'fArray'){
									$sql = $fieldName.' '.$tparam['dtype'];
									$sql .= $tparam['inull'] == 'NO' ? ' NOT ' : ' ';
									$sql .= 'NULL';
									$sql .= $tparam['dvalue'] != '' ? " DEFAULT '".$tparam['dvalue']."'" : '';
									$sql .= $tparam['autoincrement'] != '' ? ' '.$tparam['autoincrement'] : '';
									
									if($tparam['pkey'] == 'PRI'){
										$primary = true;
										$primaryKey = ' PRIMARY KEY ('.$fieldName.')';
									}
									$sql .= $primary === true ? ',' : '';
									// print "<pre>";
									// print_r($sql);
									// print "</pre>";
									$arrayNewTable[$i][] = $sql;
									
// DROP TABLE IF EXISTS `aprueba`;
// CREATE TABLE IF NOT EXISTS `aprueba` (
  // `codigo` int(1) NOT NULL AUTO_INCREMENT,
  // `nombre` varchar(20) COLLATE utf16_spanish2_ci NOT NULL,
  // `descripcion` int(11) DEFAULT NULL,
  // `estatus` tinyint(4) NOT NULL DEFAULT '1',
  // PRIMARY KEY (`codigo`)
// ) ENGINE=InnoDB;
								}
								else
								{
									$fieldPrev = '';
									$new_array = $data[$blockType];
									foreach ($new_array as $fieldName2 => $tparam2) {
										if($fieldName != $fieldName2){
											$fieldPrev = $fieldName2;
										}else{
											break;
										}
									}
									$sql = 'ALTER TABLE '.$tparam['ARRAY_KEY_1'].' ADD '.$fieldName.' '.$tparam['dtype'];
									$sql .= $tparam['inull'] == 'NO' ? ' NOT ' : ' ';
									$sql .= 'NULL';
									$sql .= $tparam['dvalue'] != '' ? " DEFAULT '".$tparam['dvalue']."' " : ' ';
									$sql .= $fieldPrev != '' ? ' AFTER '.$fieldPrev : ' ';
									$sql = trim($sql).';';
									$array_sql[] = $sql;
									// print $sql;
									
									
								}
								
							}
							
							if(isset($tparam['changeType']) && $tparam['changeType'] && $blockType == 'fArray'){
								
								$sql = 'ALTER TABLE '.$tparam['ARRAY_KEY_1'].' CHANGE '.$fieldName.' '.$fieldName.' '.$tparam['dtype'];
								$sql .= $tparam['inull'] == 'NO' ? ' NOT ' : ' ';
								$sql .= 'NULL';
								$sql .= $tparam['dvalue'] != '' ? " DEFAULT '".$tparam['dvalue']."'" : '';
								$sql .= $tparam['autoincrement'] != '' ? ' '.$tparam['autoincrement'] : '';
								$sql = trim($sql).';';
								$i++;
								$array_sql[] = $sql;
								// print $sql;
							}
						?>
                            <li <?php if (isset($tparam['isNew']) && $tparam['isNew']) {
                                echo 'style="color: red;" class="new" ';
                            } ?>><b style="white-space: pre"><?php echo $fieldName; ?></b>
                                <span <?php if (isset($tparam['changeType']) && $tparam['changeType']): ?>style="color: red;" class="new" <?php endif;?>>
                                    <?php echo $tparam['dtype']; ?>
                                </span>
                            </li>
                        <?php
						
						}
						
						if($newTable == true and $blockType == 'fArray'){
							if($primary === true) $arrayNewTable[$i][] = $primaryKey;
							
							$sql = ')';
							$sql .= isset($additionalTableInfo[$tableName][$blockType]['engine']) == true ? ' ENGINE '.$additionalTableInfo[$tableName][$blockType]['engine'].';' : ';';
							$arrayNewTable[$i][] = $sql;
							$i++;
							// print "<pre>";
							// print_r($arrayNewTable);
							// print "</pre>";
						}
						?>
                    </ul>
                <?php } ?>
                <?php if ($data != null && isset($data[$blockType]) && $data[$blockType] != null && count($data[$blockType]) && in_array($_REQUEST['action'], array('tables', 'views'))) { ?><a
                    target="_blank"
                    onclick="Data.getTableData('index.php?action=rows&baseName=<?php echo $basesName[$blockType]; ?>&tableName=<?php echo $tableName; ?>'); return false;"
                    href="#" class="sample-data">Sample data (<?php echo SAMPLE_DATA_LENGTH; ?> rows)</a><?php } ?>
            </td>
            <?php } ?>
        </tr>
    <?php } ?>
    </table>
	<div class="sql">
		<table class="table-sql">
			<?php foreach($array_sql as $value){ ?>
				<tr>
					<td><?php print $value; ?></td>
				</tr>
			<?php }
			
			foreach($arrayNewTable as $index => $value){?>
				<tr>
					<td>&nbsp;</td>
				</tr>
			<?php foreach($value as $value2){?>
				<tr>
					<td><?php echo $value2; ?></td>
				</tr>
			<?php }
			} ?>
		</table>
    </div>
	
    <p>&nbsp;</p>
    <hr />
    <p>For more information go to <a href="http://compalex.net" target="_blank">compalex.net</a></p>
</div>
</body>
