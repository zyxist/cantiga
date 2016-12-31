<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace Cantiga\Metamodel\Statistics;

/**
 * Helps generating nice color palettes for charts.
 */
class PaletteGenerator
{
	public function generatePalette($numberOfColors, $rBase, $gBase, $bBase)
	{
		$palette = [];
		if ($numberOfColors == 0) {
			return $palette;
		}
		
		$hslBase = $this->rgb2hsl([$rBase / 255.0, $gBase / 255.0, $bBase / 255.0]);
		$hslBright = $hslBase;
		$hslBright[2] += 0.25;
		
		$palette[] = ['c' => $this->rgb2hex([$rBase / 255.0, $gBase / 255.0, $bBase / 255.0]), 'h' => $this->rgb2hex($this->hsl2rgb($hslBright))];
		$baseHue = $hslBase[0];
		$step = 1.0 / (double) $numberOfColors;
		for ($i = 1; $i < $numberOfColors; ++$i) {
			$hslBase[0] = fmod($baseHue + $step * ((double) $i), 1.0);
			$hslBright[0] = $hslBase[0];
			
			$palette[] = ['c' => $this->rgb2hex($this->hsl2rgb($hslBase)), 'h' => $this->rgb2hex($this->hsl2rgb($hslBright))];
		}
		return $palette;
	}
	
	
	private function hsl2rgb($hsl)
	{
		$h = $hsl[0];
		$s = $hsl[1];
		$l = $hsl[2];
		$m2 = ($l <= 0.5) ? $l * ($s + 1) : $l + $s - $l * $s;
		$m1 = $l * 2 - $m2;
		return array($this->hue2rgb($m1, $m2, $h + 0.33333),
			$this->hue2rgb($m1, $m2, $h),
			$this->hue2rgb($m1, $m2, $h - 0.33333));
	}

	private function hue2rgb($m1, $m2, $h)
	{
		$h = ($h < 0) ? $h + 1 : (($h > 1) ? $h - 1 : $h);
		if ($h * 6 < 1) {
			return $m1 + ($m2 - $m1) * $h * 6;
		}
		if ($h * 2 < 1) {
			return $m2;
		}
		if ($h * 3 < 2) {
			return $m1 + ($m2 - $m1) * (0.66666 - $h) * 6;
		}
		return $m1;
	}

	private function rgb2hsl($rgb)
	{
		$r = $rgb[0];
		$g = $rgb[1];
		$b = $rgb[2];
		$min = min($r, min($g, $b));
		$max = max($r, max($g, $b));
		$delta = $max - $min;
		$l = ($min + $max) / 2;
		$s = 0;
		if ($l > 0 && $l < 1) {
			$s = $delta / ($l < 0.5 ? (2 * $l) : (2 - 2 * $l));
		}
		$h = 0;
		if ($delta > 0) {
			if ($max == $r && $max != $g) {
				$h += ($g - $b) / $delta;
			}
			if ($max == $g && $max != $b) {
				$h += (2 + ($b - $r) / $delta);
			}
			if ($max == $b && $max != $r) {
				$h += (4 + ($r - $g) / $delta);
			}
			$h /= 6;
		}
		return array($h, $s, $l);
	}
	
	function rgb2hex($rgb)
	{
		$hex = "#";
		$hex .= str_pad(dechex(floor($rgb[0] * 255)), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex(floor($rgb[1] * 255)), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex(floor($rgb[2] * 255)), 2, "0", STR_PAD_LEFT);

		return strtoupper($hex);
	}

}
