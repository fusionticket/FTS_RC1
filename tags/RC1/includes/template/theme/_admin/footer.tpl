{*
/**
%%%copyright%%%
 *
 * FusionTicket - Free Ticket Sales Box Office
 * Copyright (C) 2007-2013 Christopher Jenkins. All rights reserved.
 *
 * Original Design:
 * phpMyTicket - ticket reservation system
 * Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
 *
 * This file is part of FusionTicket.
 *
 * This file may be distributed and/or modified under the terms of the
 * "GNU General Public License" version 3 as published by the Free
 * Software Foundation and appearing in the file LICENSE included in
 * the packaging of this file.
 *
 * This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
 * THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE.
 *
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact info@fusionticket.com if any conditions of this licencing isn't
 * clear to you.

 * Please goto fusionticket.org for more info and help.
 */
 *}
{if $smarty.request.ajax neq "yes"}

			</div>

			<div id="footer">
				Powered by <a href="http://fusionticket.org">Fusion Ticket</a> - The Free Open Source Box Office
			</div>
		</div>
    <script type="text/javascript" language="javascript">
    	jQuery(document).ready(function(){
        //var msg = ' errors';
        var emsg = '{printMsg|escape:'quotes' key='__Warning__' addspan=false}';
        showErrorMsg(emsg);
        var nmsg = '{printMsg|escape:'quotes' key='__Notice__' addspan=false}';
        showNoticeMsg(nmsg);
        {gui->getJQuery}
      });
    </script>
    </div>
	</body>
</html>
{/if}