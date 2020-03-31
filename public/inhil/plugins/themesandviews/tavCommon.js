
/******************************************************************************
 *
 * Purpose: ThemesAndViews plugin
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/

var themesAndViewsCommon = {
	/**
	 * Get the theme or the view selected in the specified combobox
	 */
	getSelectedThemeAndViewsBox: function(boxElem) {
		var strret = "";
		var selform = _$(boxElem);
		if (selform) {
			if (selform.selgroup) {
				var seltav = selform.selgroup.options[selform.selgroup.selectedIndex].value;
				strret = seltav.replace(/^\s+/, '').replace(/\s+$/, '');
			}
		}
		return strret;
	}
};
