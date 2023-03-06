<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:76:"/www/wwwroot/www.jiajiazxgg.com/public/../webapp/admin/view/index/index.html";i:1643266947;s:69:"/www/wwwroot/www.jiajiazxgg.com/public/../webapp/admin/view/yado.html";i:1591931402;s:78:"/www/wwwroot/www.jiajiazxgg.com/public/../webapp/admin/view/common/header.html";i:1508310392;s:76:"/www/wwwroot/www.jiajiazxgg.com/public/../webapp/admin/view/common/left.html";i:1669213021;s:78:"/www/wwwroot/www.jiajiazxgg.com/public/../webapp/admin/view/common/footer.html";i:1591931576;}*/ ?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<head>
<meta charset="utf-8" />
<title><?php echo getData("basic",['id'=>1],"title"); ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1" name="viewport" />
<link rel="shortcut icon" href="__ROOT__/favicon.ico">
<link href="__JS__/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="__JS__/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
<link href="__JS__/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="__JS__/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
<link href="__CSS__/components.min.css" rel="stylesheet" id="style_components" type="text/css" />
<link href="__CSS__/plugins.min.css" rel="stylesheet" type="text/css" />
<link href="__CSS__/layout.min.css" rel="stylesheet" type="text/css" />
<link href="__CSS__/darkblue.min.css" rel="stylesheet" type="text/css" id="style_color" />
<link href="__CSS__/custom.min.css" rel="stylesheet" type="text/css" />
<!--[if lt IE 9]>
<script src="__JS__/respond.min.js"></script>
<script src="__JS__js/excanvas.min.js"></script> 
<script src="__JS__js/ie8.fix.min.js"></script> 
<![endif]-->
<script src="__JS__/jquery.min.js" type="text/javascript"></script>
<script src="__JS__/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="__JS__/js.cookie.min.js" type="text/javascript"></script>
<script src="__JS__/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="__JS__/jquery.blockui.min.js" type="text/javascript"></script>
<script src="__JS__/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<!-- 核心插件 结束 -->
<!-- 全局脚本 开始 -->
<script src="__JS__/app.min.js" type="text/javascript"></script>
<!-- 全局脚本 结束 -->
<!-- 布局脚本 开始 -->
<script src="__JS__/layout/scripts/layout.min.js" type="text/javascript"></script>
<script src="__JS__/layout/scripts/demo.min.js" type="text/javascript"></script>
<script src="__JS__/global/scripts/quick-sidebar.min.js" type="text/javascript"></script>
<script src="__JS__/global/scripts/quick-nav.min.js" type="text/javascript"></script>
<script src="__JS__/formvalidator.js"></script>
<script src="__JS__/formvalidatorregex.js"></script>

</head>
<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white">
<div class="page-wrapper">
    <div class="page-header navbar navbar-fixed-top">
	<div class="page-header-inner ">
	    	<div class="page-logo">
	        	<a href="<?php echo url('admin/index/index'); ?>">
	            	<img src="__IMG__/logo-big-white.png" alt="logo" class="logo-default" />
				</a>
	        	<div class="menu-toggler sidebar-toggler">
	                    <span></span>
	        	</div>
	     	</div>
		    <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
		      	<span></span>
		    </a>
    		<!-- div class="top-menu">
                <ul class="nav navbar-nav pull-right">
                    
                    <li class="dropdown dropdown-user">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                            <span class="username username-hide-on-mobile"> 管理员 </span>
                            <i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-default">
                            <li>
                                <a href="page_user_profile_1.html">
                                    <i class="icon-user"></i> 个人中心 </a>
                            </li>
                            <li>
                                <a href="app_inbox.html">
                                    <i class="icon-envelope-open"></i> 我的邮件
                                    <span class="badge badge-danger"> 3 </span>
                                </a>
                            </li>
                            <li class="divider"> </li>
                            <li>
                                    <a href="<?php echo url('admin/index/logout'); ?>"><i class="icon-key"></i> 退出登录 </a>
                            </li>
                        </ul>
                    </li>
                </ul>
    		</div -->
	</div>
</div>
    <div class="clearfix"> </div>
    <div class="page-container">
    <div class="page-sidebar-wrapper">
   <div class="page-sidebar navbar-collapse collapse">
		<ul class="page-sidebar-menu" data-keep-expanded="false" data-auto-scroll="false" data-slide-speed="200">
			<li class="sidebar-toggler-wrapper hide">
				<div class="sidebar-toggler">
					<span></span>
				</div>
			</li>
			<li class="sidebar-search-wrapper">
				<form class="sidebar-search  " action="#" method="POST">
					<a href="javascript:;" class="remove">
						<i class="icon-close"></i>
					</a>
					<div class="input-group" style="display:none;">
						<input type="text" class="form-control" placeholder="搜索...">
						<span class="input-group-btn">
							<a href="javascript:;" class="btn submit">
								<i class="icon-magnifier"></i>
							</a>
						</span>
					</div>
				</form>
			</li>
			<li class="nav-item <?php if($cur['controller'] == 'Index'): ?>start active open<?php endif; ?>">
				<a href="#" class="nav-link nav-toggle">
					<i class="icon-home"></i>
					<span class="title">后台首页</span>
					<span class="selected"></span>
					<span class="arrow <?php if($cur['controller'] == 'Index'): ?>start active open<?php endif; ?>"></span>
				</a>
			</li>
			<li class="heading">
				<h3 class="uppercase">管理员设置</h3>
			</li>
			<li class="nav-item <?php if($cur['controller'] == 'Member'): ?>start active open<?php endif; ?>">
				<a href="#" class="nav-link nav-toggle">
					<i class="icon-user"></i>
					<span class="title">会员管理</span>
					<span class="arrow <?php if($cur['controller'] == 'Member'): ?>open<?php endif; ?>"></span>
				</a>
				<ul class="sub-menu">
					<li class="nav-item <?php if($cur['controller'] == 'Member' && $cur['action'] != 'fx_lists' && $cur['action'] != 'pay_lists' && $cur['action'] != 'tx_lists'): ?>start active open<?php endif; ?>">
						<a href="<?php echo url('admin/member/lists'); ?>" class="nav-link">
							<span class="title">会员列表</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['controller'] == 'Member' && $cur['action'] == 'tx_lists'): ?>start active open<?php endif; ?>">
						<a href="<?php echo url('admin/member/tx_lists'); ?>" class="nav-link">
							<span class="title">提现列表</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['controller'] == 'Member' && $cur['action'] == 'sjtx_lists'): ?>start active open<?php endif; ?>">
						<a href="<?php echo url('admin/member/sjtx_lists'); ?>" class="nav-link">
							<span class="title">商家提现列表</span>
						</a>
					</li>
<!-- 					<li class="nav-item <?php if($cur['controller'] == 'Member' && $cur['action'] == 'fx_lists'): ?>start active open<?php endif; ?>">
						<a href="<?php echo url('admin/member/fx_lists'); ?>" class="nav-link">
							<span class="title">返现列表</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['controller'] == 'Member' && $cur['action'] == 'pay_lists'): ?>start active open<?php endif; ?>">
						<a href="<?php echo url('admin/member/pay_lists'); ?>" class="nav-link">
							<span class="title">线下支付记录</span>
						</a>
					</li> -->
				</ul>
			</li>
			<li class="nav-item <?php if($cur['controller'] == 'Agent'): ?>start active open<?php endif; ?>">
				<a href="#" class="nav-link nav-toggle">
					<i class="icon-user"></i>
					<span class="title">代理商管理</span>
					<span class="arrow <?php if($cur['controller'] == 'Agent'): ?>open<?php endif; ?>"></span>
				</a>
				<ul class="sub-menu">
					<li class="nav-item <?php if($cur['controller'] == 'Agent' && $cur['action'] != 'setup'): ?>start active open<?php endif; ?>">
						<a href="<?php echo url('admin/agent/lists'); ?>" class="nav-link">
							<span class="title">代理商列表</span>
						</a>
					</li>
				</ul>
			</li>

			<li class="nav-item <?php if($cur['controller'] == 'AgentGame'): ?>start active open<?php endif; ?>">
				<a href="#" class="nav-link nav-toggle">
					<i class="icon-user"></i>
					<span class="title">游戏代理商管理</span>
					<span class="arrow <?php if($cur['controller'] == 'AgentGame'): ?>open<?php endif; ?>"></span>
				</a>
				<ul class="sub-menu">
					<li class="nav-item <?php if($cur['controller'] == 'AgentGame' && $cur['action'] == 'lists'): ?>start active open<?php endif; ?>">
						<a href="<?php echo url('admin/agentGame/lists'); ?>" class="nav-link">
							<span class="title">游戏代理商列表</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['controller'] == 'AgentGame' && $cur['action'] == 'setup'): ?>start active open<?php endif; ?>">
						<a href="<?php echo url('admin/AgentGame/setup'); ?>" class="nav-link">
							<span class="title">游戏代理商设置</span>
						</a>
					</li>
				</ul>
			</li>

			<li class="nav-item <?php if($cur['controller'] == 'Order'): ?>start active open<?php endif; ?>">
				<a href="javascript:;" class="nav-link nav-toggle">
					<i class="icon-list"></i>
					<span class="title">订单管理</span>
					<span class="arrow <?php if($cur['controller'] == 'Order'): ?>open<?php endif; ?>"></span>
				</a>
				<ul class="sub-menu">
					<li class="nav-item <?php if($cur['action'] == 'lists'): ?>active<?php endif; ?>">
						<a href="<?php echo url('admin/order/lists'); ?>" class="nav-link ">
							<span class="title">订单列表</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['action'] == 'collage_order'): ?>active<?php endif; ?>">
						<a href="<?php echo url('admin/order/collage_order'); ?>" class="nav-link ">
							<span class="title">拼团订单</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['action'] == 'collage_order_a'): ?>active<?php endif; ?>">
						<a href="<?php echo url('admin/order/collage_order_a'); ?>" class="nav-link ">
							<span class="title">拼团订单新</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['action'] == 'collage_order_c'): ?>active<?php endif; ?>">
						<a href="<?php echo url('admin/order/collage_order_c'); ?>" class="nav-link ">
							<span class="title">拼团订单2</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="nav-item <?php if($cur['controller'] == 'Category' || $cur['controller'] == 'CategoryBanner'): ?>start active open<?php endif; ?>">
				<a href="javascript:;" class="nav-link nav-toggle">
					<i class="icon-list"></i>
					<span class="title">栏目管理</span>
					<span class="arrow <?php if($cur['controller'] == 'Category' || $cur['controller'] == 'CategoryBanner'): ?>open<?php endif; ?>"></span>
				</a>
				<ul class="sub-menu">
					<li class="nav-item <?php if($cur['controller'] == 'Category'): ?>active<?php endif; ?>">
						<a href="<?php echo url('admin/category/lists'); ?>" class="nav-link ">
							<span class="title">栏目列表</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['controller'] == 'CategoryBanner'): ?>active<?php endif; ?>">
						<a href="<?php echo url('admin/category_banner/lists'); ?>" class="nav-link ">
							<span class="title">banner图下导航</span>
						</a>
					</li>
				</ul>
			</li>
            <li class="nav-item <?php if($cur['controller'] == 'Content' || $cur['controller'] == 'Notice'): ?>start active open<?php endif; ?>">
                <a href="javascript:;" class="nav-link nav-toggle">
                    <i class="icon-note"></i>
                    <span class="title">内容管理</span>
                    <span class="arrow <?php if($cur['controller'] == 'Content' || $cur['controller'] == 'Notice'): ?>open<?php endif; ?>"></span>
                </a>
                <?php $oneList = db("category")->where("pid",0)->order("id ASC")->select(); ?>
                <ul class="sub-menu">
                    <?php if(is_array($oneList) || $oneList instanceof \think\Collection || $oneList instanceof \think\Paginator): $i = 0; $__LIST__ = $oneList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?>
                    <li class="nav-item <?php if($cur['controller'] == 'Content' && $cur['oid'] == $r['id']): ?>start active open<?php endif; ?>">
                        <a class="nav-link nav-toggle">
                            <i class="icon-folder-alt"></i>
                            <span class="title"><?php echo $r['name']; ?></span>
                            <span class="arrow  <?php if($cur['controller'] == 'Content' && $cur['oid'] == $r['id']): ?>open<?php endif; ?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <?php $twoList = db("category")->where("pid",$r['id'])->order("id ASC")->select(); if(is_array($twoList) || $twoList instanceof \think\Collection || $twoList instanceof \think\Paginator): $i = 0; $__LIST__ = $twoList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                            <li class="nav-item <?php if($cur['controller'] == 'Content' && $cur['tid'] == $v['id']): ?>start active open<?php endif; ?>">
                                <a class="nav-link nav-toggle">
                                    <i class="icon-folder-alt"></i>
                                    <span class="title"><?php echo $v['name']; ?></span>
                                    <span class="arrow  <?php if($cur['controller'] == 'Content' && $cur['tid'] == $v['id']): ?>open<?php endif; ?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <?php $threeList = db("category")->where("pid",$v['id'])->order("id ASC")->select(); if(is_array($threeList) || $threeList instanceof \think\Collection || $threeList instanceof \think\Paginator): $i = 0; $__LIST__ = $threeList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$a): $mod = ($i % 2 );++$i;?>
                                    <li class="nav-item <?php if($cur['controller'] == 'Content' && $cur['id'] == $a['id']): ?>open<?php endif; ?>">
                                        <a href="<?php echo url('admin/content/lists','catid='.$a['id']); ?>" class="nav-link nav-toggle">
                                            <span class="title"><?php echo $a['name']; ?></span>
                                        </a>
                                    </li>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </ul>
                            </li>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                    </li>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
<!--					<li class="nav-item <?php if($cur['controller'] == 'Content' && input('catid') == 0): ?>open<?php endif; ?>">-->
<!--						<a href="<?php echo url('admin/content/lists','catid=0'); ?>" class="nav-link nav-toggle">-->
<!--							<span class="title">保险商品</span>-->
<!--						</a>-->
<!--					</li>-->
					<li class="nav-item <?php if($cur['controller'] == 'Notice'): ?>open<?php endif; ?>">
						<a href="<?php echo url('admin/notice/lists'); ?>" class="nav-link nav-toggle">
							<span class="title">通知公告</span>
						</a>
					</li>
                </ul>
            </li>
			<li class="nav-item <?php if($cur['controller'] == 'Position'): ?>start active open<?php endif; ?>">
				<a href="javascript:;" class="nav-link nav-toggle">
					<i class="icon-list"></i>
					<span class="title">推荐管理</span>
					<span class="arrow <?php if($cur['controller'] == 'Position'): ?>open<?php endif; ?>"></span>
				</a>
				<ul class="sub-menu">
					<li class="nav-item <?php if($cur['controller'] == 'Position' && input('state') == 1): ?>active<?php endif; ?>">
						<a href="<?php echo url('admin/Position/lists','state=1'); ?>" class="nav-link ">
							<span class="title">首页人气推荐</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['controller'] == 'Position' && input('state') == 2): ?>active<?php endif; ?>">
						<a href="<?php echo url('admin/Position/lists','state=2'); ?>" class="nav-link ">
							<span class="title">购物车为你推荐</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['controller'] == 'Position' && input('state') == 3): ?>active<?php endif; ?>">
						<a href="<?php echo url('admin/Position/lists','state=3'); ?>" class="nav-link ">
							<span class="title">首页导航下推荐</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="nav-item <?php if($cur['controller'] == 'Fragment'): ?>start active open<?php endif; ?>">
				<a href="#" class="nav-link nav-toggle">
					<i class="icon-note"></i>
					<span class="title">图片管理</span>
					<span class="arrow"></span>
				</a>
				<ul class="sub-menu">
					<li class="nav-item <?php if($cur['controller'] == 'Fragment' && input('catid') == 0): ?>open<?php endif; ?>">
						<a href="<?php echo url('admin/Fragment/index','catid=0'); ?>" class="nav-link nav-toggle">
							<span class="title">推荐</span>
						</a>
					</li>
					<?php if(is_array($oneList) || $oneList instanceof \think\Collection || $oneList instanceof \think\Paginator): $i = 0; $__LIST__ = $oneList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
					<li class="nav-item <?php if($cur['controller'] == 'Fragment' && input('catid') == $v['id']): ?>open<?php endif; ?>">
						<a href="<?php echo url('admin/Fragment/index','catid='.$v['id']); ?>" class="nav-link nav-toggle">
							<span class="title"><?php echo $v['name']; ?></span>
						</a>
					</li>
					<?php endforeach; endif; else: echo "" ;endif; ?>
					<li class="nav-item <?php if($cur['controller'] == 'Fragment' && input('catid') == -1): ?>open<?php endif; ?>">
						<a href="<?php echo url('admin/Fragment/index','catid=-1'); ?>" class="nav-link nav-toggle">
							<span class="title">嘉诗娜保险</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="nav-item <?php if($cur['controller'] == 'Pay'): ?>start active open<?php endif; ?>">
				<a href="javascript:;" class="nav-link nav-toggle">
					<i class="icon-list"></i>
					<span class="title">资金流水</span>
					<span class="arrow <?php if($cur['controller'] == 'Pay'): ?>open<?php endif; ?>"></span>
				</a>
				<ul class="sub-menu">
<!-- 					<li class="nav-item <?php if($cur['controller'] == 'Pay' && $cur['action'] == 'lists'): ?>active open<?php endif; ?>">
						<a href="<?php echo url('admin/Pay/lists'); ?>" class="nav-link ">
							<span class="title">资金流水</span>
						</a>
					</li> -->
					<li class="nav-item <?php if($cur['controller'] == 'Pay' && $cur['action'] == 'alists'): ?>active open<?php endif; ?>">
						<a href="<?php echo url('admin/Pay/alists'); ?>" class="nav-link ">
							<span class="title">资金流水新</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['controller'] == 'Pay' && $cur['action'] == 'zmlists'): ?>active open<?php endif; ?>">
						<a href="<?php echo url('admin/Pay/zmlists'); ?>" class="nav-link ">
							<span class="title">转卖记录</span>
						</a>
					</li>
					
				</ul>

			</li>
			<li class="nav-item <?php if($cur['controller'] == 'Insure'): ?>start active open<?php endif; ?>">
				<a href="javascript:;" class="nav-link nav-toggle">
					<i class="icon-list"></i>
					<span class="title">保险管理</span>
					<span class="arrow <?php if($cur['controller'] == 'Insure'): ?>open<?php endif; ?>"></span>
				</a>
				<ul class="sub-menu">
					<li class="nav-item <?php if($cur['controller'] == 'Insure' && $cur['action'] == 'lists'): ?>active open<?php endif; ?>">
						<a href="<?php echo url('admin/Insure/lists'); ?>" class="nav-link ">
							<span class="title">保险记录</span>
						</a>
					</li>
					
					
					<li class="nav-item <?php if($cur['controller'] == 'Insure' && $cur['action'] == 'shenqing'): ?>active open<?php endif; ?>">
						<a href="<?php echo url('admin/Insure/shenqing'); ?>" class="nav-link ">
							<span class="title">健康金申请</span>
						</a>
					</li>
					
					
				</ul>
				
				
				
			</li>
            <li class="nav-item <?php if($cur['controller'] == 'Setup' || $cur['controller'] == 'Express' || $cur['controller'] == 'Agree'): ?>start active open<?php endif; ?>">
                <a href="" class="nav-link nav-toggle">
                    <i class="icon-users"></i>
                    <span class="title">管理员设置</span>
                    <span class="arrow <?php if($cur['controller'] == 'Setup' || $cur['controller'] == 'Express' || $cur['controller'] == 'Agree'): ?>start active open<?php endif; ?>"></span>
                </a>
                <ul class="sub-menu">
                    <li class="nav-item <?php if($cur['controller'] == 'Setup' && $cur['action'] == 'basic'): ?>start active open<?php endif; ?>">
                        <a href="<?php echo url('admin/Setup/basic'); ?>" class="nav-link ">
                            <span class="title">网站设置</span>
                        </a>
                    </li>
					
					                    <li class="nav-item <?php if($cur['controller'] == 'Setup' && $cur['action'] == 'gg'): ?>start active open<?php endif; ?>">
                        <a href="<?php echo url('admin/Setup/gg'); ?>" class="nav-link ">
                            <span class="title">提现广告设置</span>
                        </a>
                    </li>
					<li class="nav-item <?php if($cur['controller'] == 'Setup' && $cur['action'] == 'website'): ?>start active open<?php endif; ?>">
						<a href="<?php echo url('admin/Setup/website'); ?>" class="nav-link ">
							<span class="title">返现设置</span>
						</a>
					</li>

<!--					<li class="nav-item <?php if($cur['controller'] == 'Setup' && $cur['action'] == 'collage'): ?>start active open<?php endif; ?>">-->
<!--						<a href="<?php echo url('admin/Setup/collage'); ?>" class="nav-link ">-->
<!--							<span class="title">拼团 返现设置</span>-->
<!--						</a>-->
<!--					</li>-->

					<li class="nav-item <?php if($cur['controller'] == 'Express' && ($cur['action'] == 'lists' || $cur['action'] == 'edit')): ?>start active open<?php endif; ?>">
						<a href="<?php echo url('admin/Express/lists'); ?>" class="nav-link ">
							<span class="title">快递费设置</span>
						</a>
					</li>
					<li class="nav-item <?php if($cur['controller'] == 'Agree'): ?>start active open<?php endif; ?>">
						<a href="" class="nav-link nav-toggle">
							<span class="title">协议设置</span>
							<span class="arrow <?php if($cur['controller'] == 'Agree'): ?>start active open<?php endif; ?>"></span>
						</a>
						<ul class="sub-menu">
							<li class="nav-item <?php if($cur['controller'] == 'Agree' && input('id') == 1): ?>start active open<?php endif; ?>">
								<a href="<?php echo url('admin/agree/page?id=1'); ?>" class="nav-link ">
									<span class="title">店铺协议</span>
								</a>
							</li>
							<li class="nav-item <?php if($cur['controller'] == 'Agree' && input('id') == 2): ?>start active open<?php endif; ?>">
								<a href="<?php echo url('admin/agree/page?id=2'); ?>" class="nav-link ">
									<span class="title">代理商协议</span>
								</a>
							</li>
							<li class="nav-item <?php if($cur['controller'] == 'Agree' && input('id') == 4): ?>start active open<?php endif; ?>">
								<a href="<?php echo url('admin/agree/page?id=4'); ?>" class="nav-link ">
									<span class="title">游戏代理商协议</span>
								</a>
							</li>
							<li class="nav-item <?php if($cur['controller'] == 'Agree' && input('id') == 3): ?>start active open<?php endif; ?>">
								<a href="<?php echo url('admin/agree/page?id=3'); ?>" class="nav-link ">
									<span class="title">分销协议</span>
								</a>
							</li>							
							
							<li class="nav-item <?php if($cur['controller'] == 'Agree' && input('id') == 100): ?>start active open<?php endif; ?>">
								<a href="<?php echo url('admin/agree/page?id=100'); ?>" class="nav-link ">
									<span class="title">wifi用户服务协议</span>
								</a>
							</li>
							<li class="nav-item <?php if($cur['controller'] == 'Agree' && input('id') == 101): ?>start active open<?php endif; ?>">
								<a href="<?php echo url('admin/agree/page?id=101'); ?>" class="nav-link ">
									<span class="title">wifi隐私协议</span>
								</a>
							</li>
						</ul>
					</li>
                    <li class="nav-item <?php if($cur['controller'] == 'Setup' && $cur['action'] == 'admininfo'): ?>start active open<?php endif; ?>">
                        <a href="<?php echo url('admin/Setup/admininfo'); ?>" class="nav-link ">
                            <span class="title">修改登录密码</span>
                        </a>
                    </li>
                </ul>
            </li>
			<li class="nav-item <?php if($cur['controller'] == 'Calculation'): ?>start active open<?php endif; ?>">
				<a href="<?php echo url('admin/Calculation/index'); ?>" class="nav-link ">
					<i class="icon-settings"></i>
					<span class="title">压单率计算</span>
				</a>
			</li>
			
			
						<li class="nav-item <?php if($cur['controller'] == 'Wifi'): ?>start active open<?php endif; ?>">
				<a href="javascript:;" class="nav-link nav-toggle">
					<i class="icon-list"></i>
					<span class="title">WIFI设置</span>
					<span class="arrow <?php if($cur['controller'] == 'wifi'): ?>open<?php endif; ?>"></span>
				</a>
				<ul class="sub-menu">
					<li class="nav-item <?php if($cur['controller'] == 'Wifi' && $cur['action'] == 'category_lists'): ?>active open<?php endif; ?>">
						<a href="<?php echo url('admin/wifi/category_lists'); ?>" class="nav-link ">
							<span class="title">商家分类</span>
						</a>
					</li>
					
					
					<li class="nav-item <?php if($cur['controller'] == 'Wifi' && $cur['action'] == 'wifi_lists'): ?>active open<?php endif; ?>">
						<a href="<?php echo url('admin/Wifi/wifi_lists'); ?>" class="nav-link ">
							<span class="title">wiif信息</span>
						</a>
					</li>				
					
					<li class="nav-item <?php if($cur['controller'] == 'Wifi' && $cur['action'] == 'shangjia_lists'): ?>active open<?php endif; ?>">
						<a href="<?php echo url('admin/Wifi/shangjia_lists'); ?>" class="nav-link ">
							<span class="title">店铺信息</span>
						</a>
					</li>					
					
					
				</ul>
				
				
				
			</li>
			
			
			<li class="nav-item">
				<a href="<?php echo url('admin/index/logout'); ?>" class="nav-link nav-toggle">
					<i class="icon-settings"></i>
					<span class="title">退出登录</span>
				</a>
			</li>
		</ul>
	</div>
</div>
        <!-- 中间内容 开始 -->
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="index.html">后台首页</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>主面板</span>
        </li>
    </ul>
</div>
<h1 class="page-title"> 主面板</h1>
<div class="row">
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <a class="dashboard-stat dashboard-stat-v2 blue" href="index.html#">
            <div class="visual">
                <i class="fa fa-hdd-o"></i>
            </div>
            <div class="details">
                <div class="number">
                    <span data-counter="counterup" data-value="1349">0</span>
                </div>
                <div class="desc"> 网站数据量 </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <a class="dashboard-stat dashboard-stat-v2 red" href="index.html#">
            <div class="visual">
                <i class="fa fa-bar-chart-o"></i>
            </div>
            <div class="details">
                <div class="number">
                    <span data-counter="counterup" data-value="2134231">0</span></div>
                <div class="desc"> 本月访问量 </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <a class="dashboard-stat dashboard-stat-v2 green" href="index.html#">
            <div class="visual">
                <i class="fa fa-commenting"></i>
            </div>
            <div class="details">
                <div class="number">
                    <span data-counter="counterup" data-value="549">0</span>
                </div>
                <div class="desc"> 用户留言 </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <a class="dashboard-stat dashboard-stat-v2 purple" href="index.html#">
            <div class="visual">
                <i class="fa fa-globe"></i>
            </div>
            <div class="details">
                <div class="number"> +
                    <span data-counter="counterup" data-value="89"></span>% </div>
                <div class="desc"> ????? </div>
            </div>
        </a>
    </div>
</div>
<div class="clearfix"></div>
            </div>
        </div>
        <!-- 中间内容 结束 -->
    </div>
    <div class="page-footer">
        <div class="page-footer-inner">
            <a href="<?php echo getData('basic',['id'=>1],'icpurl'); ?>" target="_blank" style="color: #fff;"><?php echo getData("basic",['id'=>1],"icp"); ?></a>
        </div>
        <div class="scroll-to-top">
            <i class="icon-arrow-up"></i>
        </div>
</div>
</div>
</body>
</html>