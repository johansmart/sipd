/******************************************************************************
 *
 * Purpose: Rounded boxes plugin
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

/*
 * This is a pmapper plugin. It permit to draw rounded corners, by using the 
 * jQuery corner plugin.
 */

$.extend(PM.Plugin,
{
	RoundedBoxes: 
	{
		options: {type: 'round', size: 10},
		init: function() {
			var type = this.options.type;
			var sizeTxt = '' + this.options.size + 'px';
			var where = '';
			var what = null;

			// north, south, east, west
			what = $('#uiLayoutNorth');
			where = (parseInt(what.css('margin-top')) == 0 && parseInt(what.css('top')) == 0) ? 'bottom' : ' ';
			what.corner(type + ' ' + where + ' ' + sizeTxt);
			what = $('#uiLayoutSouth');
			where = (parseInt(what.css('margin-bottom')) == 0 && parseInt(what.css('bottom')) == 0) ? 'top' : ' ';
			what.corner(type + ' ' + where + ' ' + sizeTxt);
			what = $('#uiLayoutEast');
			where = (parseInt(what.css('margin-right')) == 0 && parseInt(what.css('right')) == 0) ? 'tl bl' : ' ';
			what.corner(type + ' ' + where + ' ' + sizeTxt);
			what = $('#uiLayoutWest');
			where = (parseInt(what.css('margin-left')) == 0 && parseInt(what.css('left')) == 0) ? 'tr br' : ' ';
			what.corner(type + ' ' + where + ' ' + sizeTxt);
			$('.ui-layout-center, #map').corner(type + ' ' + sizeTxt);
			$('.pm-tabs li span').corner(type + ' top ' + sizeTxt);
			

			this.autoCalculateCorners('.map-top-bar');
			this.autoCalculateCorners('#toolBar');
		},
		autoCalculateCorners: function(selector) {
			var type = this.options.type;
			var sizeTxt = '' + this.options.size + 'px';
			var elem = $(selector);
			elem.each(function() {
				var where = '';
				if ($(this).height() >= $(this).parent().height()) {
					if ($(this).width() >= $(this).parent().width()) {
						where += '  ';
					} else if ($(this).ileft() == 0) {
						where += ' tl bl ';
					} else if (parseInt($(this).css('right')) == 0) {
						where += ' tr br ';
					}
				}
				else if ($(this).itop() == 0) {
					if ($(this).width() >= $(this).parent().width()) {
						where += ' top ';
					} else if ($(this).ileft() == 0) {
						where += ' tl ';
					} else if (parseInt($(this).css('right')) == 0) {
						where += ' tr ';
					}
				} else if (parseInt($(this).css('bottom')) == 0) {
					if ($(this).width() >= $(this).parent().width()) {
						where += ' bottom ';
					} else if ($(this).ileft() == 0) {
						where += ' bl ';
					} else if (parseInt($(this).css('right')) == 0) {
						where += ' br ';
					}
				}
				if (where.length > 0) {
					$(this).corner(type + ' ' + where + ' ' + sizeTxt);
				}
			});
		}
	}
});
