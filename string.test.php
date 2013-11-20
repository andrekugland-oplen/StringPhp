<?php

	require_once 'string.php';
	require_once 'underscore.php';

	header('Content-Type: text/plain; charset=UTF-8');

	$source = file_get_contents('string.php');

	function test_eq($expr) {
		static $count = 1;

		echo "TEST #" . S("$count")->padLeft(2, "0") . " "
		   . (eval("return $expr;") ? "PASSED" : "FAILED")
		   . ": " . $expr
		   . "\n";

		$count++;
	}

	S($source)->matchAllCallback('/\/\*\*.*Examples?:(.*)\*\//muxsU',
		function ($index, $matches) {
			foreach (S($matches[1])->replaceRegex('/^\s*\*\s*|\s*$/m', '')->lines(-1, true) as $line) {
				test_eq($line);
				/*$a = S($line)->splitRegex('/(?= (===|!==|==|!=|<=|>=|<|>) )/');
				$lh = S($a[0])
					->replaceRegex('/("[^"]+")/m', 'S(\1)')
					->replaceRegex("/('[^']+')/m", 'S(\1)')
					->trim();
				$rh = S($a[1])->trim();
				test_eq($lh . " " . $rh);*/
			}
		}
	);

?>
