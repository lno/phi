<header id="header">
  <div class="header-top">
    <div class="holder">
      <strong class="logo"><a href="/cpanel/home.do"><img src="/assets/base/phi/images/logo.png" alt="logo">phi framework</a></strong>
      <nav id="nav">
        <a class="opener" href="#">
          <span class="opener-holder bar1">&nbsp;</span>
          <span class="opener-holder">&nbsp;</span>
          <span class="opener-holder">&nbsp;</span>
        </a>
        <?php if (!strstr($_SERVER['REQUEST_URI'], 'login')): ?>
          <ul class="js-slide-hidden" style="display: none;">
            <li><?php echo $html->link('キャッシュ管理', 'CacheManager') ?></li>
            <li><?php echo $html->link('パフォーマンスアナライザ', 'PerformanceAnalyzer') ?></li>
            <li><?php echo $html->link('DAOジェネレータ', 'GenerateDAOForm') ?></li>
            <li><?php echo $html->link('ログアウト', 'Logout') ?></li>
          </ul>
        <?php endif; ?>
      </nav>
    </div>
  </div>
</header>