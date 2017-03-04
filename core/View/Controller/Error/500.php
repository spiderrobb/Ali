<h2>500 Internal Server Error</h2>
<?php
$e = Ali\App::getLastException();
$trace = $e->getTrace();
?>
<table class="exception">
	<caption>
		<?php echo get_class($e).' ('.$e->getCode().'): '.$e->getMessage(); ?><br>
		<?php echo $e->getFile().':'.$e->getLine(); ?>
	</caption>
	<tbody>
		<?php foreach ($trace as $i => $line) { ?>
			<tr>
				<td><?php echo '#'.$i; ?></td>
				<td><?php echo $line['class'].$line['type'].$line['function'].'('.implode(', ', $line['args']).'):'.$line['line']; ?></td>
				<td><?php echo $line['file']; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<pre>
<?php
Ali\Package::getInstance()->addStyle("
table.exception {
	width:100%;
}
.exception caption {
	text-align:left;
	font-size:18px;
	font-weight:bold;
}
");