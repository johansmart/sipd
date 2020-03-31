/******************************************************************************
 *
 * Purpose: Infos functionnalities integration in pmapper
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * The software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/

function showGroupInfo(group) {
	group = group.replace('tgrp_', '');
    openInfosWin(group);
}

function openInfosWin(group) {
	var url = PM_PLUGIN_LOCATION + '/infos/infos.phtml?group=' + group + '&' + SID;
    createDnRDlg({w:350, h:200, l:100, t:50}, {resizeable:true, newsize:true}, 'pmDlgContainer', _p('Info'), url);
}