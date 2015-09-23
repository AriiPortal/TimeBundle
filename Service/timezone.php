<?
/*	File: timezone.php
 *	By: Tom Watts
 *	Email: wattst@uoguelph.ca or tomwatts@secondsite.biz
 *	GPL:
 *	This script is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU Lesser General Public License as published by
 *	the Free Software Foundation; either version 2.1 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Lesser General Public License for more details.
 *
 *	You can receive a copy of GNU Lesser General Public License at the
 *	World Wide Web address <http://www.gnu.org/licenses/lgpl.html>.
 *
 *	Description:
 *	This is a PHP script that calculates your local time via an HTML page.
 *	A drop-down menu, checkbox, and submit button allow you to choose a
 *	location and whether or not you would like to observe DST.
 *	
 *	There may be a few cases where some countries' DST change rules are off.
 *	This would only cause problems the night of the actual change to or from
 *	DST causing time to be off by an hour.  If you find an error with the
 *	DST rules as I just described, please email me with the actual
 *	rule for observation of DST so I can fix it.
 *
 *	Feel free to use/modify this script as you like.  What we ask in return
 *	is that you include a link to us (www.anicon.ca) on the webpage that
 *	you use it.  If you see something that could be improved upon, send an
 *	email to me explaining what you would like to see added/modified and
 *	I'll consider adding it.
 *	
 *	I would also like to see how this script is used, so if you do use it,
 *	email me a link to the page in which it's used.
 */

/*	Changes:
	Sept. 5, 2005
		- Changed the default time returned to be the server time
		instead of the UNIX epoch
		- Fixed the statement on database connect failure to use
		echo instead of old database error function
	Oct. 22, 2005
		- Added a current year parameter to the functions used in
		determining if daylight saving or not since some of the
		checks needed to be for the following year instead of the
		same year ie. cases 18, 68, 70, 73.
		- Some calls to the date function were lacking quotes around the
		formatting string; added those.
	Oct. 23, 2005
		- Added a year parameter for passing into functions used
		to determine if DST or not.  This year parameter is the year
		that the year that the daylight saving rule applies in
*/

/*	Function that returns the formatted time */

	function GetTime($input_location_id)
	{
	global $dst;

$Timezone[1]="-12,0,";
$Timezone[2]="-11,0,";
$Timezone[3]="-10,0,H";
$Timezone[4]="-9,1,AK";
$Timezone[5]="-8,1,P";
$Timezone[6]="-7,0,M";
$Timezone[7]="-7,1,";
$Timezone[8]="-7,1,M";
$Timezone[9]="-6,0,";
$Timezone[10]="-6,1,C";
$Timezone[11]="-6,1,";
$Timezone[12]="-6,0,C";
$Timezone[13]="-5,0,";
$Timezone[14]="-5,1,E";
$Timezone[15]="-5,0,E";
$Timezone[16]="-4,1,A";
$Timezone[17]="-4,0,";
$Timezone[18]="-4,1,";
$Timezone[19]="-3.5,1,N";
$Timezone[20]="-3,1,";
$Timezone[21]="-3,0,";
$Timezone[22]="-3,1,";
$Timezone[23]="-2,1,";
$Timezone[24]="-1,1,";
$Timezone[25]="-1,0,";
$Timezone[26]="0,0,";
$Timezone[27]="0,1,";
$Timezone[28]="1,1,";
$Timezone[29]="1,1,";
$Timezone[30]="1,1,";
$Timezone[31]="1,1,";
$Timezone[32]="1,0,";
$Timezone[33]="2,1,";
$Timezone[34]="2,1,";
$Timezone[35]="2,1,";
$Timezone[36]="2,0,";
$Timezone[37]="2,1,";
$Timezone[38]="2,0,";
$Timezone[39]="3,1,";
$Timezone[40]="3,0,";
$Timezone[41]="3,1,";
$Timezone[42]="3,0,";
$Timezone[43]="3.5,1,";
$Timezone[44]="4,0,";
$Timezone[45]="4,1,";
$Timezone[46]="4.5,0,";
$Timezone[47]="5,1,";
$Timezone[48]="5,0,";
$Timezone[49]="5.5,0,";
$Timezone[50]="5.75,0,";
$Timezone[51]="6,1,";
$Timezone[52]="6,0,";
$Timezone[53]="6,0,";
$Timezone[54]="6.5,0,";
$Timezone[55]="7,0,";
$Timezone[56]="7,1,";
$Timezone[57]="8,0,";
$Timezone[58]="8,1,";
$Timezone[59]="8,0,";
$Timezone[60]="8,0,";
$Timezone[61]="8,0,";
$Timezone[62]="9,0,";
$Timezone[63]="9,0,";
$Timezone[64]="9,1,";
$Timezone[65]="9.5,1,";
$Timezone[66]="9.5,0,";
$Timezone[67]="10,0,";
$Timezone[68]="10,1,";
$Timezone[69]="10,0,";
$Timezone[70]="10,1,";
$Timezone[71]="10,1,";
$Timezone[72]="11,0,";
$Timezone[73]="12,1,";
$Timezone[74]="12,0,";
$Timezone[75]="13,0,";

	
	/* Check for valid location ID, return 0 date if invalid */
		if ($input_location_id > 0)
		{
//			$result = mysql_query("SELECT timezoneid, gmt_offset, dst_offset, timezone_code FROM timezone WHERE timezoneid = '$input_location_id'");
//			list($timezoneid, $gmt_offset, $dst_offset, $timezone_code) = mysql_fetch_array($result);
				list($gmt_offset, $dst_offset, $timezone_code)=explode(",",$Timezone[$input_location_id]);
		}
		else
		/*	This is the default date returned upon first accessing the page */
			return date('Y-m-d H:i');

		if ($dst_offset > 0)
		{
			if (!($dst))
			{
			/*	Set the DST offset to zero if the box is not checked
				and append the standard time acronym to the timezone code */
				$dst_offset = 0;
				$timezone_code = getTimeZoneCode($timezone_code, $gmt_offset + $dst_offset, "ST");
			}
			else if (!isDaylightSaving($timezoneid, $gmt_offset))
			{
			/*	Set the DST offset to zero if the timezone is not currently
				in DST and append the standard time acronym to the timezone code */
				$dst_offset = 0;
				$timezone_code = getTimeZoneCode($timezone_code, $gmt_offset + $dst_offset, "ST");
			}
			else if ($timezone_code != '')
			/*	Leave the DST offset and append the daylight saving time acronym
				to the timezone code */
				$timezone_code = getTimeZoneCode($timezone_code, $gmt_offset + $dst_offset, "DT");
			else
			/*	Assign a timezone code */
				$timezone_code = getTimeZoneCode($timezone_code, $gmt_offset + $dst_offset, "");
		}
	/*	Does not observe DST at all */
		else
			$timezone_code = getTimeZoneCode($timezone_code, $gmt_offset + $dst_offset, "ST");

	/* Get the DST offset in minutes */
		$dst_offset *= 60;
	/* Get the GMT offset in minutes */
		$gmt_offset *= 60;
		$gmt_hour = gmdate('H');
		$gmt_minute = gmdate('i');
	/* Calculate the time in the timezone */
		$time = $gmt_hour * 60 + $gmt_minute + $gmt_offset + $dst_offset;

	/* Convert time back into hours and minutes when returning */
		return date('Y-m-d H:i', mktime($time / 60, $time % 60, 0, gmdate('m'), gmdate('d'), gmdate('Y'))) . " $timezone_code";
	}

/*	This function returns true if the specified timezone ID is in daylight
	saving time and false if it is not */

	function isDaylightSaving($timezoneid, $gmt_offset)
	{
	/*	Get the current year by geting GMT time and date and then adding
		offset */
		$gmt_minute = gmdate("i");
		$gmt_hour = gmdate("H");
		$gmt_month = gmdate("m");
		$gmt_day = gmdate("d");
		$gmt_year = gmdate("Y");
		$cur_year = date("Y", mktime($gmt_hour + $gmt_offset, $gmt_minute, 0, $gmt_month, $gmt_day, $gmt_year));

		switch ($timezoneid)
		{
	/*	North American cases: begins at 2 am on the first Sunday in April
		and ends on the last Sunday in October.  Note: Monterrey does not
		actually observe DST */
			case 4:		/*	Alaska */
			case 5:		/*	Pacific Time (US & Canada); Tijuana */
			case 8:		/*	Mountain Time (US & Canada) */
			case 10:	/*	Central Time (US & Canada) */
			case 11:	/*	Guadalajara, Mexico City, Monterrey */
			case 14:	/*	Eastern Time (US & Canada) */
			case 16:	/*	Atlantic Time (Canada) */
			case 19:	/*	Newfoundland */
				if (afterFirstDayInMonth($cur_year, $cur_year, 4, "Sun", $gmt_offset) &&
				beforeLastDayInMonth($cur_year, $cur_year, 10, "Sun", $gmt_offset))
					return true;
				else
					return false;
				break;

			case 7:		/*	Chihuahua, La Paz, Mazatlan */
				if (afterFirstDayInMonth($cur_year, $cur_year, 5, "Sun", $gmt_offset) &&
				beforeLastDayInMonth($cur_year, $cur_year, 9, "Sun", $gmt_offset))
					return true;
				else
					return false;
				break;

			case 18:	/*	Santiago, Chile */
				if (afterSecondDayInMonth($cur_year, 10, "Sat", $gmt_offset) &&
				beforeSecondDayInMonth($cur_year + 1, $cur_year, 3, "Sat", $gmt_offset))
					return true;

				else
					return false;
				break;

			case 20:	/*	Brasilia, Brazil */
				if (afterFirstDayInMonth($cur_year, $cur_year, 11, "Sun", $gmt_offset) &&
				beforeThirdDayInMonth($cur_year, $cur_year, 2, "Sun", $gmt_offset))
					return true;
				else
					return false;
				break;

			case 23:	/*	Mid-Atlantic */
				if (afterLastDayInMonth($cur_year, $cur_year, 3, "Sun", $gmt_offset) &&
				beforeLastDayInMonth($cur_year, $cur_year, 9, "Sun", $gmt_offset))
					return true;
				else
					return false;
				break;

	/*	EU, Russia, other cases: begins at 1 am GMT on the last Sunday
		in March and ends on the last Sunday in October */
			case 22:	/*	Greenland */
			case 24:	/*	Azores */
			case 27:	/*	Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London */
			case 28:	/*	Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna */
			case 29:	/*	Belgrade, Bratislava, Budapest, Ljubljana, Prague */
			case 30:	/*	Brussels, Copenhagen, Madrid, Paris */
			case 31:	/*	Sarajevo, Skopje, Warsaw, Zagreb */
			case 33:	/*	Athens, Istanbul, Minsk */
			case 34:	/*	Bucharest */
			case 37:	/*	Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius */
			case 41:	/*	Moscow, St. Petersburg, Volgograd */
			case 47:	/*	Ekaterinburg */
			case 45:	/*	Baku, Tbilisi, Yerevan */
			case 51:	/*	Almaty, Novosibirsk */
			case 56:	/*	Krasnoyarsk */
			case 58:	/*	Irkutsk, Ulaan Bataar */
			case 64:	/*	Yakutsk, Sibiria */
			case 71:	/*	Vladivostok */
				if (afterLastDayInMonth($cur_year, $cur_year, 3, "Sun", $gmt_offset) &&
				beforeLastDayInMonth($cur_year, $cur_year, 10, "Sun", $gmt_offset))
					return true;
				else
					return false;
				break;

			case 35:	/*	Cairo, Egypt */
				if (afterLastDayInMonth($cur_year, $cur_year, 4, "Fri", $gmt_offset) &&
				beforeLastDayInMonth($cur_year, $cur_year, 9, "Thu", $gmt_offset))
					return true;
				else
					return false;
				break;

			case 39:	/*	Baghdad, Iraq */
				if (afterFirstOfTheMonth($cur_year, $cur_year, 4, $gmt_offset) &&
				beforeFirstOfTheMonth($cur_year, $cur_year, 10, $gmt_offset))
					return true;
				else
					return false;
				break;

			case 43:	/*	Tehran, Iran - Note: This is an approximation to 
							the actual DST dates since Iran goes by the Persian
							calendar.  There are tools for converting between
							Gregorian and Persian calendars at www.farsiweb.info.
							This may be added at a later date for better accuracy */
				if (afterLastDayInMonth($cur_year, $cur_year, 3, "Sun", $gmt_offset) &&
				beforeLastDayInMonth($cur_year, $cur_year, 9, "Sun", $gmt_offset))
					return true;
				else
					return false;
				break;

			case 65:	/*	Adelaide */
			case 68:	/*	Canberra, Melbourne, Sydney */
				if (afterLastDayInMonth($cur_year, $cur_year, 10, "Sun", $gmt_offset) &&
				beforeLastDayInMonth($cur_year, $cur_year + 1, 3, "Sun", $gmt_offset))
					return true;
				else
					return false;
				break;

			case 70:	/*	Hobart */
				if (afterFirstDayInMonth($cur_year, $cur_year, 10, "Sun", $gmt_offset) &&
				beforeLastDayInMonth($cur_year, $cur_year + 1, 3, "Sun", $gmt_offset))
					return true;
				else
					return false;
				break;

			case 73:	/*	Auckland, Wellington */
				if (afterFirstDayInMonth($cur_year, $cur_year, 10, "Sun", $gmt_offset) &&
				beforeThirdDayInMonth($cur_year, $cur_year + 1, 3, "Sun", $gmt_offset))
					return true;
				else
					return false;
				break;

			default:
				break;
		}
		return false;
	}

/*	This function returns true if the current date (at the specified GMT
	offset) is after the first specified day of the week in specified
	month and false if it is not */
	
	function afterFirstDayInMonth($curYear, $year, $month, $day, $gmt_offset)
	{
		for ($i = 1; $i < 8; $i++)
		{
			if (date("D", mktime(0,0,0,$month,$i)) == $day)
			{
				$first_day = $i;
				break;
			}
		}
		
		$curDay = gmdate("d");
		$curMonth = gmdate("m");
		$curHour = gmdate("H") + $gmt_offset;
	/* The current time stamp */
		$cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

	/* Time stamp for the first occurence for the specified day in the month */
		$first_day_stamp = mktime(2, 0, 0, $month, $first_day, $year);
				
		if ($cur_stamp >= $first_day_stamp)
			return true;
			
		return false;
	}
	
/*	This function returns true if the current date (at the specified GMT
	offset) is before the last specified day of the week in specified
	month and false if it is not */
	
	function beforeLastDayInMonth($curYear, $year, $month, $day, $gmt_offset)
	{
		$days_in_month = getDaysInMonth($month);
		
		for ($i = $days_in_month; $i > ($days_in_month - 8); $i--)
		{
			if (date("D", mktime(0,0,0,$month,$i)) == $day)
			{
				$last_day = $i;
				break;
			}
		}
		
		$curDay = gmdate("d");
		$curMonth = gmdate("m");
		$curHour = gmdate("H") + $gmt_offset;
	/* The current time stamp */
		$cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

	/* Time stamp for the last occurrence of the day in the month at 2 am */
		$last_sun_stamp = mktime(2, 0, 0, $month, $last_day, $year);
				
		if ($cur_stamp < $last_sun_stamp)
			return true;
			
		return false;
	}

/*	This function returns true if the current date (at the specified GMT
	offset) is after the last specified day of the week in specified
	month and false if it is not */

	function afterLastDayInMonth($curYear, $year, $month, $day, $gmt_offset)
	{
		$days_in_month = getDaysInMonth($month);

		for ($i = $days_in_month; $i > ($days_in_month - 8); $i--)
		{
			if (date("D", mktime(0,0,0,$month,$i)) == $day)
			{
				$last_day = $i;
				break;
			}
		}
		
		$curDay = gmdate("d");
		$curMonth = gmdate("m");
	/* All EU countries observe the DST change at 1 am GMT */
		$curHour = gmdate("H");
	/* The current time stamp */
		$cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

	/* Time stamp for the first occurence for the specified day in the month */
		$last_day_stamp = mktime(1, 0, 0, $month, $last_day, $year);
				
		if ($cur_stamp >= $last_day_stamp)
			return true;
			
		return false;
	}

/*	This function returns true if the current date (at the specified GMT
	offset) is after the first day of the specified month and false if
	it is not */

	function afterFirstOfTheMonth($curYear, $year, $month, $gmt_offset)
	{
		$curDay = gmdate("d");
		$curMonth = gmdate("m");
		$curHour = gmdate("H") + $gmt_offset;
	/* The current time stamp */
		$cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

	/* Time stamp for the first of the month */
		$last_day_stamp = mktime(3, 0, 0, $month, 1, $year);
				
		if ($cur_stamp >= $last_day_stamp)
			return true;
			
		return false;
	}

/*	This function returns true if the current date (at the specified GMT
	offset) is before the first day of the specified month and false if
	it is not */

	function beforeFirstOfTheMonth($curYear, $year, $month, $gmt_offset)
	{
		$curDay = gmdate("d");
		$curMonth = gmdate("m");
		$curHour = gmdate("H") + $gmt_offset;
	/* The current time stamp */
		$cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

	/* Time stamp for the first of the month */
		$first_day_stamp = mktime(3, 0, 0, $month, 1, $year);
				
		if ($cur_stamp < $first_day_stamp)
			return true;
			
		return false;
	}

/*	This function returns true if the current date (at the specified GMT
	offset) is before the third occurrence of the specified day of the
	week in the specified month and false if it is not */

	function beforeThirdDayInMonth($curYear, $year, $month, $day, $gmt_offset)
	{
		$count = 0;
		
		for ($i = 1; $i < 22; $i++)
		{
			if (date("D", mktime(0,0,0,$month,$i)) == $day)
			{
				$count++;
				if ($count == 3)
				{
					$third_day = $i;
					break;
				}
			}
		}
		
		$curDay = gmdate("d");
		$curMonth = gmdate("m");
		$curHour = gmdate("H") + $gmt_offset;
	/* The current time stamp */
		$cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

	/* Time stamp for the third occurence for the specified day in the month */
		$third_day_stamp = mktime(2, 0, 0, $month, $third_day, $year);
				
		if ($cur_stamp < $third_day_stamp)
			return true;
			
		return false;
	}

/*	This function returns true if the current date (at the specified GMT
	offset) is before the second occurrence of the specified day of the
	week in the specified month and false if it is not */

	function beforeSecondDayInMonth($curYear, $year, $month, $day, $gmt_offset)
	{
		$count = 0;
		
		for ($i = 1; $i < 15; $i++)
		{
			if (date("D", mktime(0,0,0,$month,$i)) == $day)
			{
				$count++;
				if ($count == 2)
				{
					$second_day = $i;
					break;
				}
			}
		}
		
		$curDay = gmdate("d");
		$curMonth = gmdate("m");
		$curHour = gmdate("H") + $gmt_offset;
	/* The current time stamp */
		$cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

	/*	Time stamp for the second occurence of the specified day in the month;
		change in Chile occurs at midnight */
		$second_day_stamp = mktime(0, 0, 0, $month, $second_day, $year);

		if ($cur_stamp < $second_day_stamp)
			return true;
			
		return false;
	}

/*	This function returns true if the current date (at the specified GMT
	offset) is after the second occurrence of the specified day of the
	week in the specified month and false if it is not */

	function afterSecondDayInMonth($curYear, $year, $month, $day, $gmt_offset)
	{
		$count = 0;
		
		for ($i = 1; $i < 15; $i++)
		{
			if (date("D", mktime(0,0,0,$month,$i)) == $day)
			{
				$count++;
				if ($count == 2)
				{
					$second_day = $i;
					break;
				}
			}
		}
		
		$curDay = gmdate("d");
		$curMonth = gmdate("m");
		$curHour = gmdate("H") + $gmt_offset;
	/* The current time stamp */
		$cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

	/*	Time stamp for the second occurence of the specified day in the month;
		change in Chile occurs at midnight */
		$second_day_stamp = mktime(0, 0, 0, $month, $second_day, $year);

		if ($cur_stamp >= $second_day_stamp)
			return true;
			
		return false;
	}

/*	A function that returns the number of days in the specified month */

	function getDaysInMonth($month)
	{
		switch ($month)
		{
		/*	The February case, check for leap year */
			case 2:
				return (date("L")?29:28);
				break;
		/* Months with 31 days */
			case 1:
			case 3:
			case 5:
			case 7:
			case 8:
			case 10:
			case 12:
				return 31;
				break;
			default:
				return 30;
				break;
		}
	}
	
/*	This function returns a formated time zone code based on the
	value of the input code, the offset, any suffix that might apply */
	
	function getTimeZoneCode($timezone_code, $total_offset, $suffix)
	{
		if ($timezone_code == '')
		{
		/* If the code is NULL, create one */
			if ($total_offset > 0)
				return ("GMT +$total_offset");
			else if ($total_offset == 0)
				return ("GMT");
			else
				return ("GMT $total_offset");
		}
		else
		/* If not, append the suffix */
			return $timezone_code . "$suffix";
	}

?>
