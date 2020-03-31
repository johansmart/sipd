/******************************************************************************
 *
 * Purpose: Reference map auto calculate
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2009 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/
$.extend(PM.Plugin,
{
    MsAutoRefMap:
    {
		init: function() {
			// replace img src :
			var imgNewUrl = PM.ini.pluginsConfig.ms_auto_refmap.imgBaseURL ? PM.ini.pluginsConfig.ms_auto_refmap.imgBaseURL : '/ms_tmp/';
			var refmapImg = $('#refMapImg').attr('src');
			if ($.browser.msie && $.browser.version < 7) {
				// use the regexp variable to avoid js compression error
				var regexp = new RegExp('.*images\/');
				refmapImg = refmapImg.replace(regexp, imgNewUrl);
			} else {
				refmapImg = refmapImg.replace('images/', imgNewUrl);
			}
			$('#refMapImg').attr('src', refmapImg);
		}
	}
});