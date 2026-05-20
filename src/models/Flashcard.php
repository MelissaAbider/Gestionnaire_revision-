<?php
/**
 * Modèle Flashcard
 */

class Flashcard {

    private int $id;
    private int $proprietaire;
    private string $title;
    private string $subject;
    private string $theme;
    private DateTime $created_at;
    private DateTime $updated_at;

    public function __construct(int $id, int $proprietaire, string $title, string $subject, string $theme, DateTime $created_at, DateTime $updated_at) {
        $this->id = $id;
        $this->proprietaire = $proprietaire;
        $this->title = $title;
        $this->subject = $subject;
        $this->theme = $theme;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }
}
