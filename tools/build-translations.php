<?php
/**
 * Builds the translation catalogue from the source strings.
 *
 * Outputs, in VaultGate/languages/:
 *   vaultgate.pot          the template (English)
 *   vaultgate-fr_FR.po/.mo French
 *   vaultgate-es_ES.po/.mo Spanish
 *
 * Run with: php tools/build-translations.php
 *
 * @package VaultGate
 */

$root = dirname( __DIR__ );

$locales = array(
	'fr_FR' => require __DIR__ . '/fr-translations.php',
	'es_ES' => require __DIR__ . '/es-translations.php',
);

$files = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $root . '/VaultGate', FilesystemIterator::SKIP_DOTS )
);

$pattern = '#\b(__|esc_html__|esc_attr__|esc_html_e|esc_attr_e|_e|_x|_n)\s*\(\s*([\'"])((?:\\\\.|(?!\2).)*)\2#s';
$strings = array();
$refs    = array();

foreach ( $files as $file ) {
	if ( 'php' !== $file->getExtension() ) {
		continue;
	}

	$path   = $file->getPathname();
	$source = (string) file_get_contents( $path );
	$rel    = 'VaultGate/' . ltrim( str_replace( '\\', '/', substr( $path, strlen( $root . '/VaultGate/' ) ) ), '/' );

	if ( ! preg_match_all( $pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ) {
		continue;
	}

	foreach ( $matches as $match ) {
		$text   = stripcslashes( $match[3][0] );
		$offset = $match[0][1];
		$line   = substr_count( substr( $source, 0, $offset ), "\n" ) + 1;

		$strings[ $text ]                    = true;
		$refs[ $text ][ $rel . ':' . $line ] = true;
	}
}

ksort( $strings, SORT_STRING );
ksort( $refs, SORT_STRING );

$failed = false;

foreach ( $locales as $locale => $catalogue ) {
	$missing = array();

	foreach ( array_keys( $strings ) as $text ) {
		if ( ! array_key_exists( $text, $catalogue ) ) {
			$missing[] = $text;
		}
	}

	if ( array() !== $missing ) {
		$failed = true;
		echo "Missing {$locale} translations:\n";
		foreach ( $missing as $text ) {
			echo '  - ' . $text . "\n";
		}
	}

	$unused = array();

	foreach ( array_keys( $catalogue ) as $text ) {
		if ( ! array_key_exists( $text, $strings ) ) {
			$unused[] = $text;
		}
	}

	if ( array() !== $unused ) {
		echo "Obsolete {$locale} entries:\n";
		foreach ( $unused as $text ) {
			echo '  - ' . $text . "\n";
		}
	}
}

if ( $failed ) {
	exit( 1 );
}

/**
 * Escapes a string for a PO file.
 *
 * @param string $value Raw value.
 */
function vg_po_escape( string $value ): string {
	return strtr(
		$value,
		array(
			'\\' => '\\\\',
			'"'  => '\\"',
			"\n" => '\\n',
			"\r" => '\\r',
			"\t" => '\\t',
		)
	);
}

/**
 * Builds a PO header.
 *
 * @param string $locale Locale code, or an empty string for the template.
 */
function vg_po_header( string $locale ): string {
	$header = "msgid \"\"\n"
		. "msgstr \"\"\n"
		. "\"Project-Id-Version: VaultGate 1.1.4\\n\"\n"
		. "\"Report-Msgid-Bugs-To: https://github.com/zigmoon/VaultGate/issues\\n\"\n"
		. "\"POT-Creation-Date: 2026-10-27T00:00:00+00:00\\n\"\n";

	if ( '' !== $locale ) {
		$header .= "\"PO-Revision-Date: 2026-10-27T00:00:00+00:00\\n\"\n"
			. "\"Last-Translator: zigmoon\\n\"\n"
			. "\"Language-Team: " . ( 'fr_FR' === $locale ? 'Français' : 'Español' ) . "\\n\"\n"
			. "\"Language: {$locale}\\n\"\n"
			. "\"Plural-Forms: nplurals=2; plural=(n > 1);\\n\"\n";
	}

	$header .= "\"MIME-Version: 1.0\\n\"\n"
		. "\"Content-Type: text/plain; charset=UTF-8\\n\"\n"
		. "\"Content-Transfer-Encoding: 8bit\\n\"\n"
		. "\"X-Domain: vaultgate\\n\"\n";

	return $header;
}

$pot = "# Copyright (C) 2026 zigmoon\n# Distributed under the GPL-2.0-or-later license.\n" . vg_po_header( '' );

foreach ( array_keys( $strings ) as $text ) {
	$reference = '#: ' . implode( ' ', array_keys( $refs[ $text ] ) ) . "\n";
	$pot      .= "\n" . $reference . 'msgid "' . vg_po_escape( $text ) . "\"\nmsgstr \"\"\n";
}

file_put_contents( $root . '/VaultGate/languages/vaultgate.pot', $pot );

/**
 * Compiles a PO catalogue to the binary MO format.
 *
 * @param array<string, string> $entries msgid => msgstr.
 * @param string                $path    Destination.
 */
function vg_compile_mo( array $entries, string $path ): void {
	uksort( $entries, 'strcmp' );

	$count         = count( $entries );
	$data          = '';
	$original_rows = '';
	$trans_rows    = '';
	$string_offset = 28 + ( 16 * $count );

	foreach ( $entries as $msgid => $msgstr ) {
		$blob           = $msgid . "\0";
		$original_rows .= pack( 'V', strlen( $blob ) - 1 ) . pack( 'V', $string_offset + strlen( $data ) );
		$data          .= $blob;
	}

	foreach ( $entries as $msgid => $msgstr ) {
		$blob        = $msgstr . "\0";
		$trans_rows .= pack( 'V', strlen( $blob ) - 1 ) . pack( 'V', $string_offset + strlen( $data ) );
		$data       .= $blob;
	}

	$binary = pack( 'V', 0x950412de )
		. pack( 'V', 0 )
		. pack( 'V', $count )
		. pack( 'V', 28 )
		. pack( 'V', 28 + ( 8 * $count ) )
		. pack( 'V', 0 )
		. pack( 'V', 0 )
		. $original_rows
		. $trans_rows
		. $data;

	file_put_contents( $path, $binary );
}

foreach ( $locales as $locale => $catalogue ) {
	$team = 'fr_FR' === $locale ? 'Français' : 'Español';
	$name = 'fr_FR' === $locale ? 'French' : 'Spanish';

	$po = "# {$name} translation for VaultGate.\n# Distributed under the GPL-2.0-or-later license.\n" . vg_po_header( $locale );

	foreach ( array_keys( $strings ) as $text ) {
		$reference = '#: ' . implode( ' ', array_keys( $refs[ $text ] ) ) . "\n";
		$po       .= "\n" . $reference . 'msgid "' . vg_po_escape( $text ) . "\"\nmsgstr \"" . vg_po_escape( $catalogue[ $text ] ) . "\"\n";
	}

	file_put_contents( $root . '/VaultGate/languages/vaultgate-' . $locale . '.po', $po );

	$mo_header = "Project-Id-Version: VaultGate 1.1.4\n"
		. "Report-Msgid-Bugs-To: https://github.com/zigmoon/VaultGate/issues\n"
		. "PO-Revision-Date: 2026-10-27T00:00:00+00:00\n"
		. "Last-Translator: zigmoon\n"
		. "Language-Team: {$team}\n"
		. "Language: {$locale}\n"
		. "MIME-Version: 1.0\n"
		. "Content-Type: text/plain; charset=UTF-8\n"
		. "Content-Transfer-Encoding: 8bit\n"
		. "Plural-Forms: nplurals=2; plural=(n > 1);\n"
		. "X-Domain: vaultgate\n";

	$entries = array_merge( array( '' => $mo_header ), $catalogue );
	uksort( $entries, 'strcmp' );

	vg_compile_mo( $entries, $root . '/VaultGate/languages/vaultgate-' . $locale . '.mo' );

	// Read the compiled catalogue back: a broken MO is worse than no MO at all.
	$raw    = (string) file_get_contents( $root . '/VaultGate/languages/vaultgate-' . $locale . '.mo' );
	$header = unpack( 'Vmagic/Vrevision/Vcount/Voriginals/Vtranslations/Vhash_size/Vhash_offset', $raw );

	if ( 0x950412de !== $header['magic'] || count( $entries ) !== $header['count'] ) {
		echo "MO verification failed for {$locale}: bad header.\n";
		exit( 1 );
	}

	$read = array();

	for ( $i = 0; $i < $header['count']; $i++ ) {
		$original    = unpack( 'Vlength/Voffset', substr( $raw, $header['originals'] + ( $i * 8 ), 8 ) );
		$translation = unpack( 'Vlength/Voffset', substr( $raw, $header['translations'] + ( $i * 8 ), 8 ) );

		$read[ substr( $raw, $original['offset'], $original['length'] ) ] = substr( $raw, $translation['offset'], $translation['length'] );
	}

	if ( $read !== $entries ) {
		echo "MO verification failed for {$locale}: entries differ.\n";
		exit( 1 );
	}
}

echo 'Wrote ' . count( $strings ) . ' strings for ' . implode( ', ', array_keys( $locales ) ) . " (pot + po + mo, verified).\n";
