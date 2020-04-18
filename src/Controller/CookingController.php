<?php

/**
 * @author Didi Kusnadi <jalapro08@gmail.com>
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

class CookingController extends AbstractController
{
    /**
     * @var string
     */
    const BASE_URL_SOURCE = 'https://raw.githubusercontent.com/loadsmileau/php-tech-task/master/';

    /**
     * @var string
     */
    const INGREDIENTS_SOURCE = 'src/App/Ingredient/data.json';
    
    /**
     * @var string
     */
    const RECIPES_SOURCE = 'src/App/Recipe/data.json';

    /**
     * @var string
     */
    protected $today;

    /**
     * @var Serializer
     */
    protected $jsonSerializer;

    /**
     * @var array
     */
    protected $recipes;

    /**
     * @var array
     */
    protected $ingredients;

    /**
     * @var array
     */
    protected $grade = [];

    /**
     * Display available recipes
     *
     * @Route("/lunch", name="lunch")
     * @Route("/lunch/{date}", name="lunch_slug")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function index(Request $request)
    {
        $this->initAction($request);

        $recipes = $this->getRecipes();

        if (!$recipes || !count($recipes)) {
            return $this->json([
                'status' => false,
                'message' => 'Sorry recipes not available at this moment. Please check your ingredients expire date.'
            ]);
        }

        return $this->json([
            'status' => true,
            'message' => 'Congratulation, this is your lunch recipes!',
            'recipes' => $recipes
        ]);
    }

    /**
     * Display all recipes
     *
     * @Route("/recipe", name="recipe")
     * @Route("/recipes", name="recipes")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function recipe()
    {
        $recipe = $this->getSource(self::RECIPES_SOURCE);
        return $this->json(!is_array($recipe) ? [$recipe] : $recipe);
    }

    /**
     * display all ingredients
     *
     * @Route("/ingredient", name="ingredient")
     * @Route("/ingredients", name="ingredients")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ingredient()
    {
        $ingredient = $this->getSource(self::INGREDIENTS_SOURCE);
        return $this->json(!is_array($ingredient) ? [$ingredient] : $ingredient);
    }

    /**
     * init action
     * read and validate requested date
     * @param  Request $request
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function initAction($request)
    {
        $slug = $request->attributes->get('_route_params');

        if (!empty($slug)) {
            $dateObj = \DateTime::createFromFormat("Y-m-d", $slug['date']);
            if ($dateObj !== false && !array_sum($dateObj::getLastErrors())) {
                $this->today = $dateObj->format("Y-m-d");
                return;
            }
        }

        $dateObj = new DateTime();
        $this->today = $dateObj->format("Y-m-d");
    }

    /**
     * get available recipes
     *
     * @return array
     */
    protected function getRecipes()
    {

        $this->ingredients = $this->getSource(self::INGREDIENTS_SOURCE);
        if (!$this->ingredients || !count($this->ingredients)) {
            return;
        }

        $this->recipes = $this->getSource(self::RECIPES_SOURCE);
        if (!$this->recipes || !count($this->recipes) || !isset($this->recipes['recipes'])) {
            return;
        }

        return $this->getRecipesByIngredients();
    }

    
    /**
     * get recipes by ingredients
     *
     * grouping and sorting recipes based on grade
     *
     * @return array|void
     */
    protected function getRecipesByIngredients()
    {
        $lunchRecipes = [];
        foreach ($this->recipes['recipes'] as $recipe) {
            $grade = $this->getRecipeGrade($recipe);

            //do not cook recipe with expired ingredients
            if ($grade > 2) {
                continue;
            }
            $recipe['grade'] = $grade;
            $lunchRecipes[] = $recipe;
        }
        
        // sort recipes by grade
        usort($lunchRecipes, function ($prev, $next) {
            return $prev['grade'] <=> $next['grade'];
        });

        //do not let grade showing
        foreach ($lunchRecipes as $key => $recipe) {
            unset($lunchRecipes[$key]['grade']);
        }

        return $lunchRecipes;
    }

    /**
     * get recipe grade value
     *
     * @param  array $recipe
     * @return int
     */
    protected function getRecipeGrade($recipe)
    {
        $grade = [];
        foreach ($recipe['ingredients'] as $ingredient) {
            $grade[] = $this->getIngredientGrade($ingredient);
        }
        return max($grade);
    }

    /**
     * get ingredient grade based on date comparison
     *
     * grade  1: Fresh
     *        2: Less fresh
     *        3: Expired
     *
     * @param  array $ingredient
     * @return int
     */
    protected function getIngredientGrade($ingredientName)
    {
        
        //do not check an ingredient multiple time
        if (!isset($this->grade[$ingredientName])) {
            $this->grade[$ingredientName] = 1;

            $ingredient = $this->getIngredientFromFridge($ingredientName);

            //ingredient not available
            if (!$ingredient) {
                return $this->grade[$ingredientName] = 3;
            }

            //ingredient expired
            if ($this->today > $ingredient['use-by']) {
                $this->grade[$ingredientName] = 3;
            }

            //ingredient still can used
            if ($this->today <= $ingredient['use-by'] && $this->today > $ingredient['best-before']) {
                $this->grade[$ingredientName] = 2;
            }
        }
        return $this->grade[$ingredientName];
    }

    /**
     * take an ingredient from fridge
     *
     * @param  string $ingredientName
     * @return array|void
     */
    protected function getIngredientFromFridge($ingredientName)
    {
        foreach ($this->ingredients['ingredients'] as $ingredient) {
            if ($ingredient['title'] == $ingredientName) {
                return $ingredient;
            }
        }
    }

    /**
     * get source
     * @param  string $url
     * @return array|void
     */
    protected function getSource($url)
    {
        try {
            $jsonString = file_get_contents(self::BASE_URL_SOURCE . $url);
            return $this->decodeJson($jsonString);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * decode json string into array
     * @param  string $jsonString
     * @return array
     */
    protected function decodeJson($jsonString)
    {
        if (!$this->jsonSerializer) {
            $this->jsonSerializer = new Serializer(
                [new GetSetMethodNormalizer()],
                ['json' => new JsonEncoder()]
            );
        }
        return $this->jsonSerializer->decode($jsonString, 'json');
    }
}
