<div class="row">
  <div class="col-md-3" id="side-nav">
    <?php foreach ($menus as $package => $names): ?>
      <h2><?php echo $package ?></h2>
      <ul>
        <?php foreach ($names as $name => $attributes): ?>
          <li><?php echo $html->link($name, $relativeAPIPath . $attributes['anchor'], array('title' => $name)) ?></li>
        <?php endforeach ?>
      </ul>
    <?php endforeach ?>
  </div>
  <div class="col-md-9" id="article">
    <h2><?php echo $file['file']['name'] ?></h2>
    <p class="right">
      <a href="#description">Description</a> |
      <a href="#defines">Defines</a> |
      <a href="#functions">Functions</a>
    </p>

    <h3 id="description">Description</h3>
    <?php if (isset($file['file']['document']['description'])): ?>
      <?php echo str_replace(['<code>', '</code>'], ['<pre>', '</pre>'], $document->decorateText($file['file']['document']['description'])); ?>
    <?php else: ?>
      <p>この関数は現在のところ詳細な情報はありません。</p>
    <?php endif ?>
    <table class="table">
      <colgroup>
        <col class="col-description-name" />
        <col class="col-description-value" />
      </colgroup>
      <?php if (isset($file['file']['document']['tags'])): ?>
        <?php foreach ($file['file']['document']['tags'] as $type => $tagAttribute): ?>
          <?php if (is_array($tagAttribute)): ?>
            <?php foreach ($tagAttribute as $description): ?>
              <tr>
                <th class="left"><?php echo ucfirst($type) ?></th>
                <td><?php echo $document->decorateTag($type, $description) ?></td>
              </tr>
            <?php endforeach ?>
          <?php else: ?>
            <tr>
              <th class="left"><?php echo ucfirst($type) ?></th>
              <td><?php echo $document->decorateTag($type, $tagAttribute) ?></td>
            </tr>
          <?php endif ?>
        <?php endforeach ?>
      <?php endif ?>
      <tr>
        <th class="left">Source file</th>
        <td><?php echo $file['file']['relativePath'] ?></td>
      </tr>
    </table>
    <p class="right"><a href="#top">To top</a></p>

    <h3 id="defines">Defines</h3>
    <?php if (isset($file['defines'])): ?>
      <table class="table">
        <colgroup>
          <col class="col-define-name" />
          <col class="col-define-value" />
        </colgroup>
        <tr>
          <th>Define</th>
          <th>Summary</th>
        </tr>
        <?php foreach ($file['defines'] as $name => $define): ?>
          <tr>
            <td class="left"><?php echo $html->link($name, '#define_' . $name) ?></td>
            <td class="left">
              <?php if (isset($define['document']['summary'])): ?>
                <?php echo $document->decorateText($define['document']['summary']) ?>
              <?php endif ?>
            </td>
          </tr>
        <?php endforeach ?>
      </table>
    <?php else: ?>
      <p>定義されている定数はありません。</p>
    <?php endif ?>
    <p class="right"><a href="#top">To top</a></p>

    <h3 id="functions">Functions</h3>
    <?php if (isset($file['functions'])): ?>
      <table class="table">
        <colgroup>
          <col class="col-function-name" />
          <col class="col-function-summary" />
        </colgroup>
        <tr>
          <th>Function</th>
          <th>Summary</th>
        </tr>
        <?php foreach ($file['functions'] as $name => $function): ?>
          <?php if ($function['access'] !== 'private'): ?>
            <tr>
              <td><?php echo $html->link($name . '()', '#function_' . $name) ?></td>
              <td>
                <?php if (isset($function['document']['summary'])): ?>
                  <?php echo $document->decorateText($function['document']['summary']) ?>
                <?php endif ?>
              </td>
            </tr>
          <?php endif ?>
        <?php endforeach ?>
      </table>
    <?php else: ?>
      <p>定義されている関数はありません。</p>
    <?php endif ?>
    <p class="right"><a href="#top">To top</a></p>

    <?php if (isset($file['defines'])): ?>
      <h3>Define details</h3>
      <dl>
        <?php foreach ($file['defines'] as $name => $define): ?>
          <dt id="define_<?php echo $name ?>"><?php echo $name ?></dt>
          <dd>
            <div class="source"><pre><?php echo PHI_StringUtils::escape($define['statement']) ?></pre></div>
            <?php if (isset($define['document']['description'])): ?>
              <?php echo $document->decorateText($define['document']['description']) ?>
            <?php else: ?>
              <p>この定数は現在のところ詳細な情報はありません。</p>
            <?php endif ?>
            <p class="right"><a href="#defines">To defines</a></p>
          </dd>
        <?php endforeach ?>
      </dl>
    <?php endif ?>

    <h3>Function details</h3>
    <dl>
      <?php foreach ($file['functions'] as $name => $function): ?>
        <dt id="function_<?php echo PHI_StringUtils::escape($name) ?>"><?php echo PHI_StringUtils::escape($name) ?>()</dt>
        <dd>
          <div class="source"><pre><?php echo PHI_StringUtils::escape($function['statement']) ?></pre></div>
          <?php if (isset($function['document']['description'])): ?>
            <?php echo $document->decorateText($function['document']['description']) ?>
          <?php else: ?>
            <p>この関数は現在のところ詳細な情報はありません。引数のリストのみが記述されています。</p>
          <?php endif ?>
          <?php if ($function['hasParameter'] || $function['hasReturn']): ?>
            <table class="table">
              <colgroup>
                <col class="col-parameter-name" />
                <col class="col-parameter-type" />
                <col class="col-parameter-description" />
              </colgroup>
              <tr>
                <th>Property</th>
                <th>Type</th>
                <th>Description</th>
              </tr>
              <?php foreach ($function['document']['tags'] as $type => $typeAttributes): ?>
                <?php if ($type === 'param'): ?>
                  <?php foreach ($typeAttributes as $parameter => $tagAttributes): ?>
                    <tr>
                      <td><?php echo $parameter ?></td>
                      <td><?php echo $tagAttributes['type'] ?></td>
                      <td>
                        <?php if (isset($tagAttributes['description'])): ?>
                          <?php echo $document->decorateText($tagAttributes['description']) ?>
                        <?php endif ?>
                      </td>
                    </tr>
                  <?php endforeach ?>
                <?php endif ?>
              <?php endforeach ?>
              <?php foreach ($function['document']['tags'] as $type => $typeAttributes): ?>
                <?php if ($type === 'return' && $typeAttributes['type'] !== 'void'): ?>
                  <tr>
                    <td>{return}</td>
                    <td><?php echo $typeAttributes['type'] ?></td>
                    <td>
                      <?php if (isset($typeAttributes['description'])): ?>
                        <?php echo $document->decorateText($typeAttributes['description']) ?>
                      <?php endif ?>
                    </td>
                  </tr>
                <?php endif ?>
              <?php endforeach ?>
            </table>
          <?php endif ?>
          <?php if ($function['document']['hasExtraTag']): ?>
            <ul class="note">
              <?php foreach ($function['document']['tags'] as $type => $typeAttribute): ?>
                <?php if ($type !== 'param' && $type !== 'return'): ?>
                  <?php if (is_array($typeAttribute)): ?>
                    <?php foreach ($typeAttribute as $description): ?>
                      <li><?php echo ucfirst($type) . ': ' . $document->decorateTag($type, $description) ?></li>
                    <?php endforeach ?>
                  <?php else: ?>
                    <li><?php echo ucfirst($type) . ': ' . $document->decorateTag($type, $typeAttribute) ?></li>
                  <?php endif ?>
                <?php endif ?>
              <?php endforeach ?>
            </ul>
          <?php endif ?>
          <p class="right"><a href="#functions">To functions</a></p>
        </dd>
      <?php endforeach ?>
    </dl>
  </div>
</div>