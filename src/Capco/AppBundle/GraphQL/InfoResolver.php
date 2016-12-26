<?php

namespace Capco\AppBundle\GraphQL;

use League\Csv\Writer;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;

class InfoResolver
{
  public function queryStringToFields(string $requestString)
  {
      $documentNode = Parser::parse(new Source($requestString));
      $fragments = [];
      foreach ($documentNode->definitions as $definition) {
          if ($definition->kind === 'OperationDefinition') {
              return $this->foldSelectionSet($definition->selectionSet, $fragments);
          }
          $fragments[$definition->name->value] = $definition;
      }

      return [];
  }

  public function guessHeadersFromFields(array $fields)
  {
      $headers = [];
      $this->appendString('', $fields, $headers);

      return $headers;
  }

  private function foldSelectionSet($selectionSet, array $fragments = [])
  {
      $fields = [];
      foreach ($selectionSet->selections as $selectionNode) {
          if ($selectionNode->kind === 'Field') {
              $fields[$selectionNode->name->value] = !empty($selectionNode->selectionSet)
                ? $this->foldSelectionSet($selectionNode->selectionSet, $fragments)
                : true;
          } elseif ($selectionNode->kind === 'FragmentSpread') {
              $spreadName = $selectionNode->name->value;
              if (isset($fragments[$spreadName])) {
                  $fragment = $fragments[$spreadName];
                  $fields += $this->foldSelectionSet($fragment->selectionSet);
              }
          }
      }

      return $fields;
  }

  private function appendString(string $string, $array, &$result)
  {
      if (is_array($array)) {
          foreach ($array as $key => $value) {
              $this->appendString(($string !== '' ? $string.'_' : '').$key, $value, $result);
          }
          return;
      }
      $result[] = $string;
  }
}
