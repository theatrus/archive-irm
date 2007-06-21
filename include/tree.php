<?php
################################################################################
#    IRM - The Information Resource Manager
#    Copyright (C) 2003 Yann Ramin
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License (in file COPYING) for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
#
################################################################################
require_once 'include/irm.inc';
require_once 'lib/Config.php';
require_once 'include/i18n.php';
require_once 'HTML/TreeMenu.php';

function buildTree(){
	AuthCheck("post-only");
	$DB=Config::Database();

	$image_path = Config::Absloc('images');
	$userbase = Config::Absloc('users/');
	
	$menu_devices= new HTML_TreeNode(array(
		'text'=>_('Inventory'),
		'linkTarget'=>'_self',
		'link'=> $userbase . 'inventory-index.php')
	);
	$menu_computers = new HTML_TreeNode(array(
		'text'=>_('Computers'),
		'linkTarget'=>'_self',
		'link'=> $userbase . 'computers-index.php')
	);
	$menu_network = new HTML_TreeNode(array(
		'text'=>_('Network'),
		'linkTarget'=>'_self',
		'link'=> $userbase . 'networking-index.php')
	);
	$menu_software = new HTML_TreeNode(array(
		'text'=>_('Software'),
		'linkTarget'=>'_self',
		'link'=> $userbase . 'software-index.php',)
	);
	$menu_tracking = new HTML_TreeNode(array(
		'text'=>_('Tracking'),
		'linkTarget'=>'_self',
		'link'=> $userbase . 'tracking-index.php?action=display',)
	);
	$menu_reports = new HTML_TreeNode(array(
		'text'=>_('Reports'),
		'linkTarget'=>'_self',
		'link'=> $userbase . 'reports-index.php',)
	);
	$menu_setup = new HTML_TreeNode(array(
		'text'=>_('Setup'),
		'linkTarget'=>'_self',
		'link'=> $userbase . 'setup-index.php',)
	);
	$menu_prefs = new HTML_TreeNode(array(
		'text'=>_('Preferences'),
		'linkTarget'=>'_self',
		'link'=> $userbase . 'prefs-index.php',)
	);
	$menu_kb = new HTML_TreeNode(array(
		'text'=>_('Knowledge Base'),
		'linkTarget'=>'_self',
		'link'=> $userbase . 'knowledgebase-index.php',)
	);
	$menu_faq = new HTML_TreeNode(array(
		'text'=>_('FAQ'),
		'linkTarget'=>'_self',
		'link'=> $userbase . 'faq-index.php',)
	);

	$menu  = new HTML_TreeMenu();
	
	$menu->addItem($menu_devices);
	$menu->addItem($menu_tracking);
	$menu->addItem($menu_reports);
	$menu->addItem($menu_kb);
	$menu->addItem($menu_faq);
	$menu->addItem($menu_setup);
	$menu->addItem($menu_prefs);

	$menu_devices->addItem($menu_computers);
	$menu_devices->addItem($menu_network);
	$menu_devices->addItem($menu_software);

	//Computer Section
	$query="SELECT * from groups order by name";
	$data = $DB->getAll($query);

	$menu_computers->addItem(new HTML_TreeNode(array(
        	  'text'=>_('Add computer') . '...',
	          'linkTarget'=>'_self',
        	  'link'=> $userbase . 'computers-index.php?action=add'
	)));

	$menu_computers->addItem(new HTML_TreeNode(array(
        	  'text'=>_('Manage groups') . '...',
	          'linkTarget'=>'_self',
        	  'link'=> $userbase . 'setup-groups-index.php'
	)));

	foreach ($data as $result)
	{		
	      $ID = $result["ID"];
	      $name = $result["name"];
			$submenu[$ID]=new HTML_TreeNode(array(
					'text'=>$name,
					'linkTarget'=>'_self',
					'link'=>"setup-groups-members.php?id=$ID",)

				);
			$menu_computers->addItem($submenu[$ID]);

			$query="SELECT c.id, c.name from computers c, comp_group g where g.group_id=$ID  and c.id = g.comp_id order by name";
			$data2 = $DB->getAll($query);
			foreach ($data2 as $result2)
				{
				$ID2 = $result2["id"];
      	$name2 = $result2["name"];
				$submenu[$ID]->addItem(new HTML_TreeNode(array(
					'text'=>$name2,
					'linkTarget'=>'_self',
					'link'=> $userbase . 'computers-index.php?action=info&ID='.$ID2,)
				));
				}	
	}

	// Ungrouped Computers
	
	$nogroup_menu=(new HTML_TreeNode(array(
	'text'=>_('Not grouped')
	)));

	$menu_computers->addItem($nogroup_menu);
	# $query="SELECT id,name from computers where id not in (select comp_id from comp_group)";
	$query="SELECT comp_id from comp_group";
	$data = $DB->getCol($query);
	$list=implode(',',$data);
	if ($list != "")
	{
		$query="SELECT id,name from computers where id not in ($list) order by name";
	} else {
		$query="SELECT id,name from computers order by name";
	}
	$data = $DB->getAll($query);
	if (!count($data))
	{
		$data = array();
	}
	foreach ($data as $result)
	{
		$ID=$result['id'];
		$name=$result['name'];

		$nogroup_menu->addItem(
		  new HTML_TreeNode(
		    array(
        	      'text'=>$name,
	              'linkTarget'=>'_self',
        	      'link'=> $userbase . 'computers-index.php?action=info&ID='.$ID
        	    )
        	  )
        	);
	}



	// Networking section
	$query="SELECT * from networking order by name";
	$data = $DB->getAll($query);

    $menu_network->addItem(new HTML_TreeNode(array(
        	  'text'=>_('Add Network Device') . '...',
	          'linkTarget'=>'_self',
        	  'link'=> $userbase . 'networking-index.php?action=select-add'
	)));

	foreach ($data as $result)
	  {		
	      $ID = $result["ID"];
	      $name = $result["name"];
				$menu_network->addItem(new HTML_TreeNode(array(
				'text'=>$name,
				'linkTarget'=>'_self',
				'link'=> $userbase . 'networking-index.php?devicetype=networking&action=info&ID='.$ID,)
			));
	  }
	  
	// Device section
	$query="SELECT * from devices order by name";
	$data = $DB->getAll($query);
	foreach ($data as $result)
	{		
		$ID = $result["ID"];
		$name = $result["name"];
		
		if(stristr($name, " "))
		{
			$name .= _(" - Unusable device");
			$submenu[$name] = new HTML_TreeNode(array(
				'text'=> $name 
			));
			$menu_devices->addItem($submenu[$name]);
		}
		else
		{
			$submenu[$name] = new HTML_TreeNode(array(
			'text'=>$name,
			'linkTarget'=>'_self',
			'link'=> $userbase . 'device-index.php?device='.$name)
			);
			$menu_devices->addItem($submenu[$name]);
			$query="SELECT * from $name order by name";
			$devices = $DB->getAll($query);
			foreach ($devices as $device)
			{
				$deviceID = $device["ID"];
				$deviceName = $device['name'];
					$submenu[$name]->addItem(new HTML_TreeNode(array(
					'text'=>$deviceName,
					'linkTarget'=>'_self',
					'link'=> $userbase . 'device-info.php?devicetype=' . $name. '&ID=' . $deviceID,)
				));
			}
		}
	}
  
	// Software section
	$query="SELECT * from software order by name";
	$data = $DB->getAll($query);
    $menu_software->addItem(new HTML_TreeNode(array(
        	  'text'=>_('Add Software') . '...',
	          'linkTarget'=>'_self',
        	  'link'=> $userbase . 'software-index.php?action=select-add'
	)));
	foreach ($data as $result)
	  {		
	      $ID = $result["ID"];
	      $name = $result["name"];
				$menu_software->addItem(new HTML_TreeNode(array(
				'text'=>$name,
				'linkTarget'=>'_self',
				'link'=> $userbase . 'software-index.php?devicetype=software&action=info&ID='.$ID,)
				));
	  }


	$image_path = Config::Absloc('images');
	// Chose a generator. You can generate DHTML or a Listbox
	$tree = new HTML_TreeMenu_DHTML($menu, array('images' => $image_path));

	return $tree;
}
?>
