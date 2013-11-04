{*                  %%%copyright%%%
 *
 * FusionTicket - ticket reservation system
 *  Copyright (C) 2007-2013 FusionTicket Solution Limited . All rights reserved.
 *
 * Original Design:
 *	phpMyTicket - ticket reservation system
 * 	Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
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
 * Any links or references to Fusion Ticket must be left in under our licensing agreement.
 *
 * By USING this file you are agreeing to the above terms of use. REMOVING this licence does NOT
 * remove your obligation to the terms of use.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact help@fusionticket.com if any conditions of this licencing isn't
 * clear to you.
 *}<!-- $Id$ -->
{if $smarty.post.action eq 'login'}
	{user->login username=$smarty.post.username password=$smarty.post.password uri=$smarty.post.uri}
{elseif $smarty.get.action eq 'logout'}
	{user->logout}
{/if}

{if $user->logged}
  {$vermenu=[['href'=>'index.php?action=person_user', 'title'=>"{!person_user!}"],
             ['href'=>'index.php?action=person_orders', 'title'=>"{!person_orders!}"],
             ['href'=>'index.php?action=logout', 'title'=>"{!logout!}"]]  scope='root'}
                        <div class="art-box art-block">
                          <div class="art-box-body art-block-body">
                            <div class="art-bar art-blockheader">
                                <h3 class="t">{!member!}</h3>
                            </div>
                            <div class="art-box art-blockcontent">
                              <div class="art-box-body art-blockcontent-body">
                                <div>
                                  {!welcome_patron!} {$user->user_firstname} {$user->user_lastname}
                                </div>
                             		<div class="cleared"></div>
                              </div>
                            </div>
                        		<div class="cleared"></div>
                          </div>
                        </div>
{else}
                        <div class="art-box art-block">
                          <div class="art-box-body art-block-body">
                            <div class="art-bar art-blockheader">
                                <h3 class="t">{!member!}</h3>
                            </div>
                            <div class="art-box art-blockcontent">
                              <div class="art-box-body art-blockcontent-body">
                                <div>
                                  <form action="{$_SHOP_root}index.php" id='login-form' name='' method='post' class='loginform' enctype='application/x-www-form-urlencoded'>
                                    {ShowFormToken name='login-form'}
                                    <input type="hidden" name="action" value="login">
                                    <input type="hidden" name="type" value="block">
                                    {if $smarty.get.action neq "logout" and $smarty.get.action neq "login"}
                                      <input type="hidden" name="uri" value="{$smarty.server.REQUEST_URI}">
                                    {/if}
                                    <p id="form-login-username">
                                  		<label for="modlgn-username">{!email!}</label><input type='text' id="modlgn-username" name='username' required='required' size="30"/>{printMsg key='loginusername'}
                                  	</p>
                                  	<p id="form-login-password">
                                  		<label for="modlgn-passwd">{!password!}</label><input id="modlgn-passwd" type='password' name='password' required='required' size="30"/>{printMsg key='loginpassword'}
                                    </p>
                                   	<div id="form-shop-remember">
                                      <ul>
                                        <li><a href='index.php?action=register'>{!register!}</a></li>
                                    		<li><a onclick='showDialog(this);return false;' href='forgot_password.php'>{!forgot_pwd!}</a></li>
                                    	</ul>
                                    </div>
                                    <div class='gui_footer'>
                                      <button  type='submit' name='submit' id='_submit' class=' admin-button ui-state-default  link  ui-corner-all admin-button-text ' style='float:right;'>{!login_button!}</button>
                                    </div>
                                  </form>

                                </div>
                             		<div class="cleared"></div>
                              </div>
                            </div>
                        		<div class="cleared"></div>
                          </div>
                        </div>
{/if}