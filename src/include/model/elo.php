<?php

class Elo
{
    /**
     * How strong a match will impact the players’ ratings
     * @var int The K Factor used.
     */
    const KFACTOR = 40;

    public function new_rating($rating_a, $rating_b, $result)
    {
        $win_chance = 1 / (1 + (pow(10, ($rating_b - $rating_a) / 400)));

        return round(self::KFACTOR * ($result - $win_chance));
    }
}
