<?php
/**
 * JLcomments
 *
 * @version 2.7.6
 * @author Kunitsyn Vadim (vadim@joomline.ru), Artem Jukov (artem@joomline.ru)
 * @copyright (C) 2011-2018 by Kunitsyn Vadim(http://www.joomline.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 **/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgContentJlcomments extends JPlugin
{

	public function onContentPrepare($context, &$article, &$params, $page = 0){
		if($context == 'com_content.article'){
		if (strpos($article->text, '{jlcomments-off}') !== false) {
			$article->text = str_replace("{jlcomments-off}","",$article->text);
			return true;
		}

		if (strpos($article->text, '{jlcomments}') === false && !$this->params->def('autoAdd')) {
			return true;
		}

		$exceptcat = is_array($this->params->def('categories')) ? $this->params->def('categories') : array($this->params->def('categories'));
		if (!in_array($article->catid,$exceptcat)) {
			$view = JRequest::getCmd('view');
			if ($view == 'article') {
				$doc = JFactory::getDocument();
				$uri = JURI::getInstance();
				$base = $uri->toString(array('scheme', 'host', 'port'));
				$article_url = $base.JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->catslug));
				

				$apiId 			= $this->params->def('apiId');
				$widthvk 		= $this->params->def('widthvk');
				$widthfb 		= $this->params->def('widthfb');
				$widthgoogle 	= $this->params->def('widthgoogle');
				$comLimit 		= $this->params->def('comLimit');
				$attach 		= $this->params->def('attach');
				$autoPublish	= $this->params->def('autoPublish');
				$norealtime 	= $this->params->def('norealtime');
				$fbId 			= $this->params->def('fbId');
				$fbadmin 		= $this->params->def('fbadmin');
				$fb_lang 		= $this->params->def('fb_lang');
				$typeviewerjq 	= $this->params->def('typeviewerjq');
				$typeviewerbs 	= $this->params->def('typeviewerbs');
				$typeviewernojq = $this->params->def('typeviewernojq');
				$typeviewercss	= $this->params->def('typeviewercss');
				$link 			= $this->params->def('link');
				$colorscheme 	= $this->params->def('colorscheme');
				$order_by_fb	= $this->params->def('orderbyfb');
				$linknone		= '';
				$lang = JFactory::getLanguage();
				$lang->load('plg_content_jlcomments', JPATH_ADMINISTRATOR);
				$jlcomments_comments = JText::_('PLG_JLCOMMENTS_COMMENTS');
				$jlcomments_vk = JText::_('PLG_JLCOMMENTS_VK');
				$jlcomments_fb = JText::_('PLG_JLCOMMENTS_FB');
				$jlcomments_google = JText::_('PLG_JLCOMMENTS_GOOGLE');				
				$pagehash = $article->id;
				if ($this->params->def('showfacebook')) {
					$doc->addCustomTag('<meta property="fb:admins" content="'.$fbadmin.'"/>');
					$doc->addCustomTag('<meta property="fb:app_id" content="'.$fbId.'"/>');
				}
				else {}

				if ($this->params->def('showvkontakte')) {				
					$doc->addCustomTag('
					<script type="text/javascript">
					jQuery.getScript(
					  "//vk.com/js/api/openapi.js",
					  function() {

						VK.init({apiId: '.$apiId.',onlyWidgets:true});
						
						VK.Widgets.Comments(
						"jlcomments", {
							limit: '.$comLimit.', 
							width: "'.$widthvk.'", 
							attach: "'.$attach.'", 
							autoPublish: '.$autoPublish.', 
							norealtime: '.$norealtime.'
							},'.$pagehash.');
						
						VK.Api.call(
							"widgets.getComments", {
								 widget_api_id: "'.$apiId.'",
								 page_id: '.$pagehash.',
								 v: "5.75"
							},
							function(r) {								
								var countvk = r.response.count;
								vk_comments_count
								jQuery("#vk_comments_count").html(countvk);
							}
						);
					  }
					);
					</script>
					');										
				}
				else {}
				if  ($this->params->def('googleplus')) {
					$doc->addCustomTag('<script src="https://apis.google.com/js/plusone.js" type="text/javascript" async="async">/* {lang: "'.$fb_lang.'"} */</script>');
				}
				else {}

				
				$orders = explode(",",$this->params->def('orders'));

				if (($this->params->def('googleid')!='')&&($this->params->def('googleplus'))){
					$doc->addCustomTag('<link rel="author" href="https://plus.google.com/'. $this->params->def('googleid') .'/">');
				}
				else {}
				
				if ($link==0){
					$linknone = 'display:none;';
					}
				else {}
				
				If ($typeviewerjq==1) {
					$doc->addCustomTag('<script src="//yandex.st/jquery/1.9.1/jquery.min.js"></script>');
					}
				If ($typeviewercss==1) {
					$doc->addStyleSheet("/plugins/content/jlcomments/css/jlcomtabs.css");
					}
				If ($typeviewerbs==1) {
					$doc->addCustomTag('<script src="//yandex.st/bootstrap/2.3.0/js/bootstrap.min.js" async="async"></script>');
					}
				If ($typeviewernojq==1) {
					$doc->addCustomTag ('<script type="text/javascript">var jqjlcomm = jQuery.noConflict();</script>');
					}
				else {}
				
                $comments = JPATH_SITE . '/components/com_jcomments/jcomments.php';
                  if (file_exists($comments)) {
                    require_once($comments);
                    $options['object_id'] = $pagehash;
                    $options['object_group'] = 'com_content';
                    $options['published'] = 1;
                    $count = JCommentsModel::getCommentsCount($options);
                  }
				
	$scriptPage = <<<HTML
		<div style="clear:both">&nbsp;</div><div id="jlcomments_container"><ul class="nav nav-tabs" id="plgjlcomments1">
HTML;


 
	foreach ($orders as $order) {
		
		switch($order) {
			case 1:	if ($this->params->def('showjcomments')) { $scriptPage .= <<<HTML
				<li style="list-style-type: none;" class="active"><a href="#jcommentscomm" data-toggle="tab"><i class="jlico-comments"></i> $jlcomments_comments ($count)</a></li>
HTML;
			} else {$scriptPage .='';$showjcomments='-1';} break;
			case 2:	if ($this->params->def('showvkontakte')) { $scriptPage .= <<<HTML
				<li style="list-style-type: none;"><a href="#vkcomm" data-toggle="tab"><i class="jlico-vk"></i> $jlcomments_vk (<span id="vk_comments_count"></span>)</a></li>
HTML;
			} else {$scriptPage .='';} break;
			case 3:	if ($this->params->def('googleplus')) {
				$width_google = $widthgoogle;
				$doc = JFactory::getDocument();
				$style1 = '#googlecomm * {width:'.$width_google.' !important;min-height:600px !important;}';
				$doc->addStyleDeclaration( $style1 );
				$scriptPage .= <<<HTML
					<li style="list-style-type: none;"><a href="#googlecomm" data-toggle="tab"><i class="jlico-google"></i> $jlcomments_google</a></li>
HTML;
			} else {$scriptPage .='';} break;
			case 4:	if ($this->params->def('showfacebook')) {$doc = JFactory::getDocument();
				$style2 = '.fb-comments,.fb-comments span,.fb-comments iframe {min-height: 300px!important; width:100%!important;} .fb_hide_iframes iframe {left:0px !important;}';
				$doc->addStyleDeclaration( $style2 );
			$scriptPage .= <<<HTML
				
				<li style="list-style-type: none;"><a href="#fbcomm" data-toggle="tab"><div><i class="jlico-facebook"></i> $jlcomments_fb (<span class="fb-comments-count" data-href="$article_url"></span>)</div></a></li>
HTML;
			} else {$scriptPage .='';} break;
			}			
			}

		$scriptPage .= <<<HTML
			</ul>
			<div class="tab-content">
HTML;
	foreach ($orders as $order) {		
		switch($order) {		
			case 1: if ($this->params->def('showjcomments')) { $scriptPage .= <<<HTML
				<div class="tab-pane active" id="jcommentscomm">
					{jcomments}
				</div>
HTML;
			} else {$scriptPage .='';$showjcomments='-1';} break;
			case 2: if ($this->params->def('showvkontakte')) { $scriptPage .= <<<HTML
				<div class="tab-pane" id="vkcomm">
					<div id="jlcomments"></div>
				</div>
								
HTML;
			} else {$scriptPage .='';} break;	
			case 3: if ($this->params->def('googleplus')) { 
				
				$scriptPage .= <<<HTML
					<div class="tab-pane" id="googlecomm">		
		
						<div class="g-comments"
							data-href="$article_url"
							data-width="500"
							data-first_party_property="BLOGGER"
							data-view_type="FILTERED_POSTMOD">
						</div>
					</div>
HTML;
			} else {$scriptPage .='';} break;						
			case 4: if ($this->params->def('showfacebook')) { $scriptPage .= <<<HTML
                <div class="tab-pane" id="fbcomm">
						<script type="text/javascript">/* <![CDATA[ */(function(d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0];
						if (d.getElementById(id)) return;
						js = d.createElement(s); js.id = id;
						js.src = "//connect.facebook.net/$fb_lang/all.js#xfbml=1&version=v2.0";
						fjs.parentNode.insertBefore(js, fjs);
						}(document, 'script', 'facebook-jssdk'));/* ]]> */</script>
					<div class="fb-comments" data-href="$article_url" data-num-posts="$comLimit" data-width="$widthfb" data-colorscheme="$colorscheme" data-order-by="$order_by_fb"></div>
				</div>				
				

HTML;
			} else {$scriptPage .='';} break;
		}
	}
	$scriptPage .= <<<HTML
		 </div>	
	</div>
		<script type="text/javascript">			
			jQuery(document).ready(function(){
			jQuery('#plgjlcomments1 a:first').tab('show');
			});			
		</script>
HTML;
		if (in_array($pagehash, array(1,6,17,33,44,64,89,166,248,593,781,1111,2174))){
					$scriptPage .= <<<HTML
					<div style="text-align: right; $linknone;">
						<a style="text-decoration:none; color: #c0c0c0; font-family: arial,helvetica,sans-serif; font-size: 5pt; " target="_blank" href="http://www.38i.ru">http://www.38i.ru</a>
					</div>					
HTML;
		}
		if ($this->params->def('autoAdd') == 1) {
			$article->text .= $scriptPage;
			} else {
				$article->text = str_replace("{jlcomments}",$scriptPage,$article->text);
				}

			}
		} else {
				$article->text = str_replace("{jlcomments}","",$article->text);
			}

		}
	}
}
