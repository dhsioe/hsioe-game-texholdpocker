<?php

/**
 * 房间桌子实例(Desk)
 * @author: hsioe1111@gmail.com
 * @Date: 2022/05/29 
 * @Description: 
 */

namespace TexPocker;

use TexPocker\Pocker\CardManager;

class Desk
{
    /**
     * 桌子上的公共牌
     * 
     * @var array
     */
    protected $commuityCards = [];

    /**
     * 小盲金额
     * 
     * @var int
     */
    protected $sbCoin = 20;

    protected $bbCoin = 40;

    protected $ntCoin = 10;

    /**
     *  椅子队员的玩家
     * 
     * @var array [chair => Player, ...]
     */
    protected $chairToPlayers = [];

    protected $lastDealerChair = 0;

    protected $lastSbChair = 0;

    protected $lastBbChair = 0;

    protected $chairCount = 0;

    protected $playerCount = 0;

    protected $actionChair = 0;

    protected $playingChairs = [];

    /**
     * 最后一位操作玩家椅子
     * 
     * @var int
     */
    protected $lastActionChair = 0;

    /**
     * 发牌器
     * 
     * @var CardManager
     */
    protected $cardManager;

    public function __construct(int $chairCount, int $sbCoin = 20, int $bbCoin = 40)
    {
        $this->chairCount = $chairCount;
        $this->sbCoin = $sbCoin;
        $this->bbCoin = $bbCoin;
        $this->cardManager = new CardManager();
    }

    public function setChairCount(int $count)
    {
        $this->chairCount = $count;
    }

    public function setCardManager($cardManager)
    {
        $this->cardManager = $cardManager;
    }

    /**
     * 设置庄位
     * 默认第一位玩家坐庄, 后续从房主的下一位开始轮流
     * 
     * @param int $round 当前游戏轮数
     */
    public function setDealer(int $round)
    {
        $this->lastDealerChair = ($this->lastDealerChair % $this->chairCount) + 1;

        if (!$round === 1) {
            while (!$this->chairToPlayers[$this->lastDealerChair]) {
                $this->lastDealerChair = ($this->lastDealerChair % $this->chairCount) + 1;
            }
        }

        $this->chairToPlayers[$this->lastDealerChair]->isDealer = true;
    }

    /**
     * 设置小盲
     */
    public function setSb()
    {
        $this->lastSbChair = ($this->lastDealerChair % $this->chairCount) + 1;

        while (!$this->chairToPlayers[$this->lastSbChair]) {
            $this->lastSbChair = ($this->lastSbChair % $this->chairCount) + 1;
        }

        $this->chairToPlayers[$this->lastSbChair]->isSb = true;
    }

    /**
     * 设置大盲
     */
    public function setBb()
    {
        $this->lastBbChair = (($this->lastDealerChair + 1) % $this->chairCount) + 1;

        while (!$this->chairToPlayers[$this->lastBbChair]) {
            $this->lastBbChair = ($this->lastBbChair % $this->chairCount) + 1;
        }

        $this->chairToPlayers[$this->lastBbChair]->isBb = true;
    }

    /**
     * 更新最后一位操作玩家
     * 
     * @param int $currentActionChair 当前操作的玩家座位
     */
    public function setLastActionChair(int $currentActionChair)
    {
        $this->lastActionChair = ($currentActionChair + $this->chairCount - 1) % $this->chairCount;

        if ($this->lastActionChair === 0) {
            $this->lastActionChair = $this->chairCount;
        }

        while (!$this->chairToPlayers[$this->lastActionChair]) {
            $this->setLastActionChair($this->lastActionChair);
        }

        return $this;
    }

    /**
     * 判断当前操作玩家是否为最后一位
     */
    public function isLastActionChair(int $currentActionChair)
    {
        return $this->lastActionChair === $currentActionChair;
    }

    /**
     * 获取最后一位操作玩家的座位
     * 
     * @var int
     */
    public function getLastActionChair(): int
    {
        return $this->lastActionChair;
    }

    /**
     * 椅子初始化
     */
    protected function initDesk()
    {
        for ($i = 0; $i < $this->chairCount; $i++) {
            $this->chairToPlayers[$i + 1] = null;
        }
    }

    /**
     * 初始化正在游戏的玩家数据
     */
    public function initPlayingChairs()
    {
        $this->playingChairs = [];
        foreach ($this->chairToPlayers as $chair => $player) {
            if ($player) {
                $this->playingChairs[$chair] = [
                    'coinPool' => 0,
                    'betCoin' => 0
                ];
            }
        }
    }

    /**
     *  每局游戏开始时
     */
    public function whenGameSetStart()
    {
        $this->initPlayingChairs();
        $this->cardManager->resetCards();
        $this->commuityCards = [];
    }

    /**
     *  当有玩家弃牌时
     */
    public function whenChairFold(int $chair)
    {
        if (key_exists($chair, $this->playingChairs)) {
            unset($this->playingChairs[$chair]);
        }
    }

    /**
     * 获取当前可以行动的玩家数
     */
    public function getPlayingChairCount(): int
    {
        return count($this->playingChairs);
    }
}
