<!-- ***ToDo*** 自分画面と他人画面での表示物の切り替え -->

	<!-- ifFollowing Follower 自分画面では不要 -->
	<!-- <span>Following: <?php echo $this->escape($headerUser['ifFollowing']); ?></span><br> -->
	<!-- <span>Follower: <?php echo $this->escape($headerUser['ifFollower']); ?></span><br> -->
<!--
	<?php if ($headerUser['usNo'] !== $user['usNo']){ ?>
		<?php if ($headerUser['ifFollowing'] == 0){ ?>
			<button type="submit" class="btn btn-warning btn-lg">Follow</button>
		<?php } else { ; ?>
			<button type="submit" class="btn btn-warning btn-lg">unFollow</button>
			<br>
		<?php }; ?>
	<?php }; ?>
 -->
<div class="container">
	<div class="row">
		<div id="mainUser">
			<div class="mainStatus" class="col-xs-8">
				<div class="simpleStatus">
					<a href="#">?</a><br>
					<span>ON/OFF</span><br>
					<!-- ***ToDo*** follow status & followButton -->
				</div><!-- simpleStatus -->
				<div class="userCenterArea">
					<div class="userImageArea">
						<img src="<?php echo $base_url .'/../user/img/'. $headerUser['usImg']; ?>" alt="user_photo">
						<p>ID:<?php echo $this->escape($headerUser['usId']); ?></p>
					</div><!-- userImageArea -->
					<div class="userInfoArea">
						<div class="myUserBalloon">
							<p>
								<span class="myCountBalloon"><?php echo $this->escape($headerUser['toMeClkSum']); ?></span>
							</p>
						</div>
						<div class="clearBoth">	</div>
						<div class="userInfoLeft">
							<p><?php echo $this->escape($headerUser['roundPt']); ?> Pt</p>
						</div><!-- userInfoLeft -->
					</div><!-- userInfoArea -->
					<div class="clearBoth">	</div>
					<div class="userInfoBottom">
						<p>■■■■■■■■■■■■■■■</p>
						<p><?php echo $this->escape($headerUser['usName']); ?></p>
					</div>
							<!-- No: <?php echo $this->escape($headerUser['usNo']); ?><br> -->
				</div><!-- userCenterArea -->
			</div><!-- mainStatus -->
			<div class="userButtonArea" class="col-xs-4">
				<div class="userButton">
					<button type="submit" id="clickAction_<?php echo $headerUser['usNo']; ?>" class="myClickAction" onclick="clickAction('post', '<?php echo $headerUser['usNo']; ?>', '<?php echo $headerUser['usId']; ?>', '<?php echo $headerUser['usName']; ?>' )"><span id="clickSum_<?php echo $headerUser['usNo']; ?>" class="countNumber"><?php echo $this->escape($headerUser['toMeClkSum']); ?></span><br>My Happy!</button>
				</div><!-- userButton -->
				<div class="userGraph">
					<canvas id="persentGraphCanvas_<?php echo $headerUser['usNo']; ?>" class="myCikCanvas" width="300" height="70">
						Canvasに対応したブラウザが必要です。</canvas>
				</div><!-- userGraph -->
			</div><!-- userButtonArea -->
			<div class="clearBoth">	</div>
		</div><!-- mainUser -->
	</div><!-- row -->
</div><!-- container -->

<script type="text/javascript">
// document.write(thisTimeTheyClickPercent[viewNo]);
viewNo++;
</script>

<!--
	TodayClick  ForYou / All: <?php echo $this->escape($headerUser['toMeClkSum']); ?> / <?php echo $this->escape($headerUser['allClkSum']); ?><br>


	ThisTimeTheyClickPercent: <span id="clickPercent_<?php echo $headerUser['usNo']; ?>">%</span>


	MySendClkSum: <span id="clickSum_<?php echo $headerUser['usNo']; ?>"><?php echo $this->escape($headerUser['toMeClkSum']); ?></span> / <?php echo $this->escape($headerUser['allClkSum']); ?>
-->
