	<script type="text/javascript">
		// 変数定義
		// var usNo = statuses[viewNo].usNo;
	</script>

	<div class="container">
		<div class="row">
			<div class="viewUsers">
				<div class="mainStatus" class="col-xs-8">
					<div class="simpleStatus">
						<a href="#">?</a><br>
						<span>ON/OFF</span><br>
						<!-- <span>Following: <?php echo $this->escape($status['ifFollowing']); ?></span> -->
			<?php
						echo $this->render('status/follow_status', array('base_url' => $base_url, 'status' => $status,));
						echo $this->render('status/follow_button', array('base_url' => $base_url, 'status' => $status, 'follow_token'=> $follow_token,));
			?>
						<!-- <span>Follower: <?php echo $this->escape($status['ifFollower']); ?></span><br> -->
						<p>No:<?php echo $this->escape($status['usNo']); ?></p>
					</div><!-- simpleStatus -->

					<div class="userCenterArea">
						<div class="userImageArea">
							<img src="<?php echo $base_url .'/../user/img/'. $status['usImg']; ?>" alt="user_photo">
							<p>ID:<?php echo $this->escape($status['usId']); ?></p>
						</div><!-- userImageArea -->
						<div class="userInfoArea">
							<div class="userBalloon">
								<p><?php echo $this->escape($status['toMeClkSum']); ?></p>
							</div>
							<div class="clearBoth">	</div>
							<div class="userInfoLeft">
								<!-- <p>all: <?php echo $this->escape($status['allClkSum']); ?></p> -->
								<p><?php echo $this->escape($status['roundPt']); ?> Pt</p>
							</div><!-- userInfoLeft -->
						</div><!-- userInfoArea -->
						<div class="clearBoth">	</div>
						<div class="userInfoBottom">
							<p>■■■■■■■■■■■■■■■</p>
							<p><?php echo $this->escape($status['usName']); ?></p>
						</div>
					</div><!-- userCenterArea -->

				</div><!-- mainStatus -->
				<div class="userButtonArea" class="col-xs-4">
					<div class="userButton">
						<!-- class="btn btn-warning btn-lg"  -->
						<button type="submit" id="clickAction_<?php echo $status['usNo']; ?>" class="clickAction" onclick="clickAction('post', '<?php echo $status['usNo']; ?>', '<?php echo $status['usId']; ?>', '<?php echo $status['usName']; ?>' )">
						<span id="clickSum_<?php echo $status['usNo']; ?>" class="countNumber"><?php echo $this->escape($status['MySendClkSum']); ?></span><br>Happy!</button>
						<!-- <?php echo $this->escape($allClkSum); ?> -->
					</div><!-- userButton -->
					<!-- <div class="clearBoth">	</div> -->
					<div class="userGraph">
						<canvas id="persentGraphCanvas_<?php echo $status['usNo']; ?>" class="myCikCanvas" width="300" height="70">Canvasに対応したブラウザが必要です。</canvas>
					</div><!-- userGraph -->
				</div><!-- userButtonArea -->
				<div class="clearBoth">	</div>
	<!--
				Id: <?php echo $this->escape($status['usId']); ?><br>
				No: <?php echo $this->escape($status['usNo']); ?><br>
				ThisTimeTheyClickPercent: <span id="clickPercent_<?php echo $status['usNo']; ?>">
				<script type="text/javascript">
					document.write(thisTimeTheyClickPercent[viewNo]);
				</script>%</span>
	 -->
			</div><!-- viewUsers -->
		</div><!-- row -->
	</div><!-- container -->
	<script type="text/javascript">
		viewNo++;
	</script>
	<!-- <div class="clearBoth">	</div> -->
