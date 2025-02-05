// @flow
import type { LogicJumpConditionOperator } from '~relay/ReplyForm_questionnaire.graphql';
import type { MultipleChoiceQuestionValidationRulesTypes } from '~relay/responsesHelper_question.graphql';
import type { ReactSelectValue } from '~/components/Form/Select';

export type QuestionType =
  | 'button'
  | 'checkbox'
  | 'editor'
  | 'medias'
  | 'number'
  | 'majority'
  | 'radio'
  | 'ranking'
  | 'section'
  | 'select'
  | 'text'
  | 'textarea'
  | 'siret'
  | 'rna';

export type Media = {|
  +id: string,
  +name: string,
  +url: string,
  +size: string,
|};

export type Value = {|
  labels: Array<string>,
  other?: string | null,
|};

export type Field = {|
  id: string,
  label: string,
  description?: string,
  color?: string,
  image?: {
    url: string,
  },
  useIdAsValue?: boolean,
|};

export type Fields = {|
  id: string,
  type: string,
  isOtherAllowed: boolean,
  choices: Array<Field>,
  checked: boolean,
  helpText?: string,
  description?: string,
|};

export type ConditionalJumpCondition = {|
  +id: string,
  +operator: LogicJumpConditionOperator,
  +question: {|
    +id: string,
    +title: string,
    +type: QuestionType,
  |},
  +value: {|
    +id: string,
    +title: string,
  |},
|};

export type Jump = {|
  +id: string,
  +origin: {|
    +id: string,
  |},
  +destination: {|
    +id: string,
    +title: string,
    +number: number,
  |},
  +conditions: $ReadOnlyArray<ConditionalJumpCondition>,
|};

export type DestinationJump = {|
  +id: string,
  +origin: {|
    +id: string,
    +title: string,
  |},
|};

export type QuestionChoice = {|
  +id: string,
  +title: string,
  +description: ?string,
  +color: ?string,
  +image: ?{|
    +id: string,
    +url: string,
  |},
|};

// This is a cp/paster of
// responsesHelper_question without $refType
export type Question = {|
  +__typename: string,
  +hidden: boolean,
  +id: string,
  +title: string,
  +number: number,
  +private: boolean,
  +level?: ?number,
  +position: number,
  +required: boolean,
  +helpText: ?string,
  +alwaysJumpDestinationQuestion: ?{|
    +id: string,
    +title: string,
    +number: number,
  |},
  +jumps: $ReadOnlyArray<Jump>,
  +destinationJumps: $ReadOnlyArray<DestinationJump>,
  +description: ?string,
  +type: QuestionType,
  +isOtherAllowed?: boolean,
  +randomQuestionChoices?: boolean,
  +validationRule?: ?{|
    +type: MultipleChoiceQuestionValidationRulesTypes,
    +number: number,
  |},
  +choices?: {|
    +pageInfo: {|
      +hasNextPage: boolean,
    |},
    +totalCount: number,
    +edges: ?$ReadOnlyArray<?{|
      +node: ?QuestionChoice,
    |}>,
  |},
  +isRangeBetween?: boolean,
  +rangeMin?: ?number,
  +rangeMax?: ?number,
  +groupedResponsesEnabled?: boolean,
  +responseColorsDisabled?: boolean,
|};

export type Questions = $ReadOnlyArray<Question>;

export type ResponsesFromAPI = $ReadOnlyArray<?{|
  +$fragmentRefs?: any,
  +question: {|
    +id: string,
    +level?: ?number,
    +title?: string,
    +__typename?: string,
  |},
  +value?: ?string,
  +medias?: $ReadOnlyArray<{|
    +id: string,
    +name: string,
    +url: string,
    +size: string,
  |}>,
|}>;

export type MultipleChoiceQuestionValue = {|
  labels: $ReadOnlyArray<string>,
  other: ?string,
|};

export type ResponseInReduxForm = {|
  question: string,
  value:
    | MultipleChoiceQuestionValue
    | ReactSelectValue
    | ?string
    | ?number
    | $ReadOnlyArray<Media | string>,
|};

export type ResponsesInReduxForm = $ReadOnlyArray<ResponseInReduxForm>;

// The real type is
//
// type SubmitResponses = $ReadOnlyArray<{|
//   value: string,
//   question: string,
// |} | {|
//   question: string,
//   medias: $ReadOnlyArray<string>,
// |}>;
export type SubmitResponses = $ReadOnlyArray<{|
  value?: any,
  question: string,
  medias?: ?$ReadOnlyArray<string>,
|}>;

export type ResponseError = {
  value: string,
};

export type ResponseWarning = ?{
  value: string,
};

export type ResponsesWarning = Array<?ResponseWarning>;
export type ResponsesError = Array<?ResponseError>;

export const OPERATOR: { IS: 'IS', IS_NOT: 'IS_NOT' } = {
  IS: 'IS',
  IS_NOT: 'IS_NOT',
};
