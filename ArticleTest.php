<?php 

use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase {

  private $article;

  public function setUp(): void {
    require_once 'Article.php';
    $this->article = new Article(1);
  }

  //On incrémente pas
  public function testIncrementerNull() {
    $this->assertEquals($this->article->getNbrLikes(), 0);
    $this->assertEquals($this->article->getNbrDislikes(), 0);
  }

  // On incrémente de 1 le like d'un article.
  public function testIncrementerLikes1() {
    $this->article->incrementerLikes('Eliott');
    $this->assertEquals($this->article->getNbrLikes(), 1);
  }

  // On incrémente de 2 le like d'un article.
  public function testIncrementerLikes2() {
    $this->article->incrementerLikes('Eliott');
    $this->article->incrementerLikes('Anass');
    $this->assertEquals($this->article->getNbrLikes(), 2);
  }

  //Un utilisateur like deux fois. Il like pour supprime son like.
  public function testIncrementerLikes3() {
    $this->article->incrementerLikes('Eliott');
    $this->assertEquals($this->article->getNbrLikes(), 1);
    $this->article->incrementerLikes('Eliott');
    $this->assertEquals($this->article->getNbrLikes(), 0);
  }

  //Un utilisateur like puis dislike. le like est supprimé et le dislike est ajouté.
  public function testIncrementerLikes4() {
    $this->article->incrementerLikes('Eliott');
    $this->assertEquals($this->article->getNbrLikes(), 1);
    $this->article->incrementerDisLikes('Eliott');
    $this->assertEquals($this->article->getNbrLikes(), 0);
    $this->assertEquals($this->article->getNbrDislikes(), 1);
  }
  
  //on incrémente de 1 dislike
  public function testIncrementerDislikes1() {
    $this->article->incrementerDisLikes('Eliott');
    $this->assertEquals($this->article->getNbrDislikes(), 1);
  }

  //on incrémente de 2 dislikes
  public function testIncrementerDislikes2() {
    $this->article->incrementerDisLikes('Eliott');
    $this->assertEquals($this->article->getNbrDislikes(), 1);
    $this->article->incrementerDisLikes('Anass');
    $this->assertEquals($this->article->getNbrDislikes(), 2);
  }

  //Un utilisateur Dislike deux fois. Il Dislike pour supprime son Dislike.
  public function testIncrementerDislikes3() {
    $this->article->incrementerDisLikes('Eliott');
    $this->assertEquals($this->article->getNbrDislikes(), 1);
    $this->article->incrementerDisLikes('Eliott');
    $this->assertEquals($this->article->getNbrDislikes(), 0);
  }

  //Un utilisateur dislike puis like. le dislike est supprimé et le like est ajouté.
  public function testIncrementerDislikes4() {
    $this->article->incrementerDisLikes('Eliott');
    $this->assertEquals($this->article->getNbrDislikes(), 1);
    $this->article->incrementerLikes('Eliott');
    $this->assertEquals($this->article->getNbrDislikes(), 0);
    $this->assertEquals($this->article->getNbrLikes(), 1);
  }

  //l'utilisateur Eliott like. Vérifie s'il est bien ajouté dans la liste des utilisateurs liké
  public function testALikeTrue() {
    $this->article->incrementerLikes('Eliott');
    $this->assertTrue($this->article->aLike('Eliott'));
  }

  //Vérifie si un utilisateur n'ayant pas liké ne serait pas présent dans la liste des utilisateurs ayant liké.
  public function testALikeFalse() {
    $this->article->incrementerLikes('Anass');
    $this->assertFalse($this->article->aLike('Eliott'));
  }

  public function testALikeFalse2() {
    $this->assertFalse($this->article->aLike('Eliott'));
  }

  public function testADislikeTrue() {
    $this->article->incrementerDisLikes('Eliott');
    $this->assertTrue($this->article->aDislike('Eliott'));
  }

  public function testADislikeFalse() {
    $this->article->incrementerDisLikes('Anass');
    $this->assertFalse($this->article->aDislike('Eliott'));
  }

  public function testADislikeFalse2() {
    $this->assertFalse($this->article->aDislike('Eliott'));
  }
}


