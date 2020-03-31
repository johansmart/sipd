/*****************************************************************************
 *
 * Purpose: add info link to layer/group in toc
 * Author:  Armin Burger
 *
 *****************************************************************************
 *
 * Copyright (c) 2003-2009 Armin Burger
 *
 * This file is part of p.mapper.
 *
 * p.mapper is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * p.mapper is distributed in the hope that it will be useful,
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
    TocInfolink:
    {
        defaults: {
            insert: 'before',
            linkimg: 'images/infolink.gif'
        },
        
        addInfoLinks: function() {
            var options = typeof(PM.Plugin.TocInfolink.options)!='undefined' ? $.extend(this.defaults, PM.Plugin.TocInfolink.options) : this.defaults;
            var groupList = typeof(options.groupList)!='undefined' ? options.groupList : PM.ini.map.allGroups.group;
            $('#toc').find('.grp-title').each(function() {
                grpName = $(this).parent().id().replace(/spxg_/, '');
                if ($.inArray(grpName, groupList) > -1) { 
                    var link = $('<a href="javascript:PM.Custom.showGroupInfo(\'' + grpName + '\')"><img class="tocinfolink-img" src="' + options.linkimg + '" alt=""></a>');
					// avoid multiple insert if many call to PM.Toc.tocPostLoading for instance
					if ($(this).parent().parent().find('.tocinfolink-img').length == 0) {
						if (options.insert == 'before') {
							link.insertBefore($(this).parent());
						} else {
							link.insertAfter($(this).parent());
						}
					}
                }
            });
        }
    }
});
