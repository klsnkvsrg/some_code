<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\Localization\Loc;
?>

<div class="b-vision">
	<div class="b-vision-content">
		<form action="<?=$APPLICATION->GetCurPage(false);?>" method="POST" name="vision_calculator" id="vision_calculator">
			<div id="title" class="b-title-block"><?=$arResult['TITLE']?></div>
			<div id="top_text"><?=$arResult['TOP_TEXT'];?></div>
			<div id="result" class="b-vision-result">
				<? if (is_array($arResult['ITEMS']) && !empty($arResult['ITEMS'])): ?>
					<? foreach ($arResult['ITEMS'] as $iKey => $arItem): ?>
						<div class="b-item">
							<? if (!empty($arItem['VALUES'])): ?>
								<? foreach ($arItem['VALUES'] as $vKey => $arValue): ?>
									<div class="b-item-value">
										<label class="b-label">
											<input type="radio" name="<?=$arItem['CONTROL_NAME']?>" value="<?=$arValue['VALUE']?>" />
											<span class="b-radio-icon"></span>
											<span class="b-label-text"><?=$arValue['TEXT']?></span>
										</label>
									</div>
								<? endforeach; ?>
							<? endif; ?>
						</div>
					<? endforeach; ?>
				<? endif; ?>
			</div>
			<div id="error" style="display: none;" class="b-error"><?=$arResult['ERROR'];?></div>
			<div id="note" style="display: none;" class="b-note"><?=$arResult['NOTE'];?></div>
			<div class="b-actions">
				<div>
					<button id="back" type="submit" name="actions" data-value="back" <? if ($arResult['STEP'] == 1): ?> disabled<? endif; ?> class="g-button back hidden"><?=Loc::getMessage('VISION_BACK_STEP')?></button><button id="next" type="submit" name="actions" data-value="next" class="g-button next"><?=Loc::getMessage('VISION_NEXT_STEP')?></button>
				</div>
				<div class="restart-holder">
					<button id="restart" type="submit" name="actions" data-value="restart" class="g-button restart"><?=Loc::getMessage('VISION_RESTART')?></button>
				</div>
			</div>
			<? if ($_REQUEST['kdev'] == 'Y'): ?>
				<input type="text" name="step" value="<?=$arResult['STEP']?>" id="step" />
				<textarea name="chain" id="chain" cols="30" rows="10"></textarea>
			<? else: ?>
				<input type="hidden" name="step" value="<?=$arResult['STEP']?>" id="step" />
				<input type="hidden" name="chain" value="" id="chain" />
			<? endif; ?>
		</form>
	</div>
	<div class="b-vision-scheme"><img src="<?=$templateFolder;?>/img/huis-watts-vision.jpg" alt="image" /></div>
</div>
<script>
	var visionCalc = new visionCalculator('vision_calculator');
</script>