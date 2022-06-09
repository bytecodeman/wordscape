<?php

class WordscapeAnalyzer {
	
	private static function isWord($word) {
		try {
			$dsn = 'mysql:host=' . WORDS_DB_HOST . ';dbname=' . WORDS_DB_NAME;
			$pdo = new PDO($dsn, WORDS_DB_USER, WORDS_DB_PASS);
			$sql = 'SELECT IF( EXISTS( SELECT * FROM words WHERE word = :word), 1, 0) As isWord';
			$stmt = $pdo->prepare($sql);
			$stmt->execute(['word' => $word]);
			return boolval($stmt->fetchColumn(0));
		} catch (Exception $e) {
			echo 'Connection failed: ' . $e->getMessage();
		}
		return FALSE;
	}

	public static function outputSolutions($results, $justRealWords) {
		$i = 0;
		$solutions = "";
		if ($justRealWords === "justRealWords") {
			$results = array_filter($results, function($value) { return $value[1]; });
		}
		if (count($results) !== 0) {
			$solutions .= "<table id=\"resultsTable\" class=\"table table-striped  table-sm mt-4\">\n";
			$solutions .= "<thead><tr><th>#</th><th>Word</th></thead>\n";
			$solutions .= "<tbody>\n";
			foreach ($results as $result) {
				$realWord = $result[1];
				if ($realWord)
					$wordSpan = "<span class=\"isWord\">" . $result[0] . "</span>";
				else
					$wordSpan = $result[0];
				$i++;
				$solutions .= "<tr>\n";
				$solutions .= "<td>" . $i . "</td>\n";
				$solutions .= "<td>" . $wordSpan . "</td>\n";
				$solutions .= "</tr>\n";
			}
			$solutions .= "</tbody></table>";
		}
		return $solutions;
	}

	private static function generateLetterPattern($letters, $numbers) {
		$values = [];
		$i = 0;
		while ($i < count($numbers))
		{
			$values[] = $letters[$numbers[$i]];
			$i++;
		}
		return $values;
	}
	
	// Not used or needed
	private static function makeWord($pattern, $values) {
		$result = "";
		$i = 0;
		$patternChars = str_split($pattern);
		foreach ($patternChars as $char) {
			if ($char === "_") {
				$result .= $values[$i++];
			}
			else {
				$result .= $char;
			}
		}
		return $result;
	}

	private static function evaluate($values, $pattern) {
		$results = "";
		for ($i = 0; $i < strlen($pattern); $i++) {
			if ($pattern[$i] !== "_" && $pattern[$i] !== " ")
				if ($pattern[$i] !== $values[$i])
					return null;
				else
					$results .= $values[$i];
			else 
				$results .= $values[$i]; 
		}
		return $results;
	}
	
	private static function searchArray($key, $results) {
		foreach ($results as $result) {
			if ($key === $result[0])
				return TRUE;
		}
		return FALSE;
	}

	public static function getWordscapeSolutions($letters, $pattern) {
		$results = [];
		$perm = new Permutations(strlen($pattern), 0, strlen($letters) - 1);
		while ($perm->hasNext()) {
			$rawValues = $perm->next();
			$values = WordscapeAnalyzer::generateLetterPattern($letters, $rawValues);	
			$result = WordscapeAnalyzer::evaluate($values, $pattern);
			if ($result != null) {
				if (!WordscapeAnalyzer::searchArray($result, $results))
					$results[] = [$result, WordscapeAnalyzer::isword($result)];
			}
		}
		usort($results, function ($a, $b) { return $a[0] <=> $b[0]; });
		return $results;
	}

}
