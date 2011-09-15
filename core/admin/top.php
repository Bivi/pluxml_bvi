<?php if(!defined('PLX_ROOT')) exit; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $plxAdmin->aConf['default_lang'] ?>" lang="<?php echo $plxAdmin->aConf['default_lang'] ?>">
<head>
	<meta name="robots" content="noindex, nofollow" />
	<title><?php echo plxUtils::strCheck($plxAdmin->aConf['title']) ?> <?php echo L_ADMIN ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET) ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/base.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/style.css" media="screen" />
	<script type="text/javascript" src="<?php echo PLX_CORE ?>lib/functions.js"></script>
	<script type="text/javascript" src="<?php echo PLX_CORE ?>lib/visual.js"></script>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminTopEndHead')) ?>
</head>

<body>

<div id="wrapper">

	<div id="container">

		<div id="sidebar">

			<div class="menus">

				<ul class="nav top">
					<li>
						<a href="<?php echo PLX_ROOT ?>" class="homepage" title="<?php echo L_BACK_TO_SITE_TITLE ?>"><?php echo L_BACK_TO_SITE;?></a>
						<?php if(isset($plxAdmin->aConf['homestatic']) AND !empty($plxAdmin->aConf['homestatic'])) : ?>
						&nbsp;|&nbsp;&nbsp;<a href="<?php echo PLX_ROOT ?>blog.php" class="homeblog" title="<?php echo L_BACK_TO_BLOG_TITLE ?>"><?php echo L_BACK_TO_BLOG;?></a>
						<?php endif; ?>
					</li>
					<li><a href="auth.php?d=1" title="<?php echo L_ADMIN_LOGOUT_TITLE ?>" id="logout"><?php echo L_ADMIN_LOGOUT ?></a></li>
				</ul>
			</div>

			<div class="user">
				<p>
					<?php //echo L_LOGIN; ?>
					<span class="username"><?php echo plxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']) ?></span>
					<?php
						if($_SESSION['profil']==PROFIL_ADMIN) printf('<em>(%s)</em>',L_PROFIL_ADMIN);
						elseif($_SESSION['profil']==PROFIL_MANAGER) printf('<em>(%s)</em>',L_PROFIL_MANAGER);
						elseif($_SESSION['profil']==PROFIL_MODERATOR) printf('<em>(%s)</em>',L_PROFIL_MODERATOR);
						elseif($_SESSION['profil']==PROFIL_EDITOR) printf('<em>(%s)</em>',L_PROFIL_EDITOR);
						else printf('<em>(%s)</em>',L_PROFIL_WRITER);
					?>
				</p>

			</div>

			<div class="menus">

				<ul class="nav bottom">
				<?php
					$menus = array();

					$menus[] = plxUtils::formatMenu(L_MENU_ARTICLES, 'index.php?page=1', L_MENU_ARTICLES_TITLE);
					
					if(isset($_GET['a'])) # edition article
						$menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, 'article.php', L_MENU_NEW_ARTICLES, false, false, '', false);
					else # nouvel article
						$menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, 'article.php', L_MENU_NEW_ARTICLES);
						
					$menus[] = plxUtils::formatMenu(L_MENU_MEDIAS, 'medias.php', L_MENU_MEDIAS_TITLE);

					if($_SESSION['profil'] <= PROFIL_MANAGER) {
						$menus[] = plxUtils::formatMenu(L_MENU_STATICS, 'statiques.php', L_MENU_STATICS_TITLE);
					}
					if($_SESSION['profil'] <= PROFIL_MODERATOR) {
						$menus[] = plxUtils::formatMenu(L_MENU_COMMENTS, 'comments.php?page=1', L_MENU_COMMENTS_TITLE);
					}
					if($_SESSION['profil'] <= PROFIL_EDITOR) {
						$menus[] = plxUtils::formatMenu(L_MENU_CATEGORIES,'categories.php', L_MENU_CATEGORIES_TITLE);
					}
					if($_SESSION['profil'] == PROFIL_ADMIN) {
						$menus[] = plxUtils::formatMenu(L_MENU_CONFIG, 'parametres_base.php', L_MENU_CONFIG_TITLE, false, false, '', false);

						if (preg_match('/parametres/',basename($_SERVER['SCRIPT_NAME']))) {
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_BASE,'parametres_base.php', L_MENU_CONFIG_BASE_TITLE, 'submenus');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_VIEW,'parametres_affichage.php', L_MENU_CONFIG_VIEW_TITLE, 'submenus');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_USERS,'parametres_users.php', L_MENU_CONFIG_USERS_TITLE, 'submenus');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_ADVANCED,'parametres_avances.php', L_MENU_CONFIG_ADVANCED_TITLE, 'submenus');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_PLUGINS,'parametres_plugins.php', L_MENU_CONFIG_PLUGINS_TITLE, 'submenus');
							$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_INFOS,'parametres_infos.php', L_MENU_CONFIG_INFOS_TITLE, 'submenus');
						}
					}
					$menus[] = plxUtils::formatMenu(L_MENU_PROFIL, 'profil.php', L_MENU_PROFIL_TITLE);

					# récuperation des menus pour les plugins
					uksort($plxAdmin->plxPlugins->aPlugins, create_function('$a, $b', 'return strcmp($b["title"], $a["title"]);'));
					foreach($plxAdmin->plxPlugins->aPlugins as $plugName => $plugin) {
						if(isset($plugin['activate']) AND $plugin['activate'] AND !empty($plugin['title'])) {
							if(isset($plugin['instance']) AND is_file(PLX_PLUGINS.$plugName.'/admin.php')) {
								if($plxAdmin->checkProfil($plugin['instance']->getAdminProfil(),false)) {
									$menus[] = plxUtils::formatMenu(plxUtils::strCheck($plugin['title']), 'plugin.php?p='.$plugName, plxUtils::strCheck($plugin['title']));
								}
							}
						}
					}

					# Hook Plugins
					eval($plxAdmin->plxPlugins->callHook('AdminTopMenus'));

					echo implode('', $menus);
				?>
				</ul>

			</div>

			<div class="pluxml"><a title="PluXml" href="http://pluxml.org">Pluxml <?php echo $plxAdmin->aConf['version'] ?></a></div>

		</div><!-- sidebar -->

		<div id="content">

			<h1 id="sitename"><?php echo plxUtils::strCheck($plxAdmin->aConf['title']) ?></h1>

			<?php
				if(is_file(PLX_ROOT.'install.php')) echo L_WARNING_INSTALLATION_FILE;
				plxMsg::Display();
			?>
