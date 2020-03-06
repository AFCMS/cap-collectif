// @flow
import * as React from 'react';
import { type IntlShape } from 'react-intl';
import { fetchQuery, graphql } from 'react-relay';
import { Field, type FieldArrayProps } from 'redux-form';
import type { QuestionTypeValue } from '~relay/ProposalPageEvaluation_proposal.graphql';
import type { LogicJumpConditionOperator } from '~relay/ReplyForm_questionnaire.graphql';
import TitleInvertContrast from '~/components/Ui/Typography/TitleInvertContrast';
import { checkOnlyNumbers } from '~/services/Validator';
import component from '~/components/Form/Field';
import select from '~/components/Form/Select';
import PrivateBox from '~/components/Ui/Boxes/PrivateBox';
import ConditionalJumps from './ConditionalJumps';
import WYSIWYGRender from '~/components/Form/WYSIWYGRender';
import invariant from './invariant';
import type {
  MultipleChoiceQuestionValidationRulesTypes,
  QuestionChoiceColor,
} from '~relay/responsesHelper_question.graphql';
import environment from '~/createRelayEnvironment';
import type { ReactSelectValue } from '~/components/Form/Select';
import type { QuestionnaireAdminConfigurationForm_questionnaire } from '~relay/QuestionnaireAdminConfigurationForm_questionnaire.graphql';
import { cleanDomId } from '~/utils/string';
import { TYPE_FORM } from '~/constants/FormConstants';
import stripHtml from '~/utils/stripHtml';
import config from '~/config';

const MULTIPLE_QUESTION_CHOICES_COUNT_TRIGGER_SEARCH = 20;

const MULTIPLE_QUESTION_CHOICES_SEARCH_QUERY = graphql`
  query responsesHelper_MultipleQuestionChoicesSearchQuery($questionId: ID!, $term: String!) {
    node(id: $questionId) {
      ... on MultipleChoiceQuestion {
        choices(term: $term) {
          edges {
            node {
              id
              title
            }
          }
        }
      }
    }
  }
`;

// eslint-disable-next-line no-unused-vars
const ResponseFragment = {
  response: graphql`
    fragment responsesHelper_response on Response {
      question {
        id
      }
      ... on ValueResponse {
        value
      }
      ... on MediaResponse {
        medias {
          id
          name
          size
          url
        }
      }
    }
  `,
};

/**
 * Ok we have two shared fragment for questions :
 * - responsesHelper_adminQuestion
 * - responsesHelper_question
 *
 * Because we need different configurations depending on frontend or backend…
 * We could use a variable (eg: isOnAdmin)
 * But this is currently not supported on shared fragment:
 * https://github.com/facebook/relay/issues/2118
 */

// eslint-disable-next-line no-unused-vars
const QuestionAdminFragment = {
  adminQuestion: graphql`
    fragment responsesHelper_adminQuestion on Question {
      __typename
      id
      title
      number
      private
      position
      required
      helpText
      jumps(orderBy: { field: POSITION, direction: ASC }) {
        id
        origin {
          id
        }
        destination {
          id
          title
          number
        }
        conditions {
          id
          operator
          question {
            id
            title
            type
          }
          ... on MultipleChoiceQuestionLogicJumpCondition {
            value {
              id
              title
            }
          }
        }
      }
      alwaysJumpDestinationQuestion {
        id
        title
        number
      }
      description
      type
      ... on MultipleChoiceQuestion {
        isOtherAllowed
        randomQuestionChoices
        validationRule {
          type
          number
        }
        choices(allowRandomize: false) {
          pageInfo {
            hasNextPage
          }
          # this is updated
          totalCount
          edges {
            node {
              id
              title
              description
              color
              image {
                id
                url
              }
            }
          }
        }
      }
    }
  `,
};

// eslint-disable-next-line no-unused-vars
const QuestionFragment = {
  question: graphql`
    fragment responsesHelper_question on Question {
      __typename
      id
      title
      number
      private
      position
      required
      helpText
      jumps(orderBy: { field: POSITION, direction: ASC }) {
        id
        origin {
          id
        }
        destination {
          id
          title
          number
        }
        conditions {
          id
          operator
          question {
            id
            title
            type
          }
          ... on MultipleChoiceQuestionLogicJumpCondition {
            value {
              id
              title
            }
          }
        }
      }
      alwaysJumpDestinationQuestion {
        id
        title
        number
      }
      description
      type
      ... on MultipleChoiceQuestion {
        isOtherAllowed
        randomQuestionChoices
        validationRule {
          type
          number
        }
        choices(allowRandomize: true) {
          pageInfo {
            hasNextPage
          }
          totalCount
          edges {
            node {
              id
              title
              description
              color
              image {
                id
                url
              }
            }
          }
        }
      }
    }
  `,
};

type ConditionalJumpCondition = {|
  +id: ?string,
  +operator: LogicJumpConditionOperator,
  +question: {|
    +id: string,
    +title: string,
    +type: QuestionTypeValue,
  |},
  +value?: ?{|
    +id: string,
    +title: string,
  |},
|};

type Jump = {|
  +id: ?string,
  +origin: {|
    +id: string,
  |},
  +destination: {|
    +id: string,
    +title: string,
    +number: number,
  |},
  +conditions: ?$ReadOnlyArray<?ConditionalJumpCondition>,
|};

export type QuestionChoice = {|
  +id: string,
  +title: string,
  +description: ?string,
  +color: ?QuestionChoiceColor,
  +image: ?{|
    +id: string,
    +url: string,
  |},
|};

// This is a cp/paster of
// responsesHelper_question without $refType
export type Question = {|
  +__typename: string,
  +id: string,
  +title: string,
  +number: number,
  +private: boolean,
  +position: number,
  +required: boolean,
  +helpText: ?string,
  +alwaysJumpDestinationQuestion: ?{|
    +id: string,
    +title: string,
    +number: number,
  |},
  +jumps: ?$ReadOnlyArray<?Jump>,
  +description: ?string,
  +type: QuestionTypeValue,
  +isOtherAllowed?: boolean,
  +randomQuestionChoices?: boolean,
  +validationRule?: ?{|
    +type: MultipleChoiceQuestionValidationRulesTypes,
    +number: number,
  |},
  +choices?: ?{|
    +pageInfo: {
      +hasNextPage: boolean,
    },
    +totalCount: number,
    +edges: ?$ReadOnlyArray<?{|
      +node: ?QuestionChoice,
    |}>,
  |},
|};
export type Questions = $ReadOnlyArray<Question>;

type ResponsesFromAPI = $ReadOnlyArray<?{|
  +$fragmentRefs?: any,
  +question: {|
    +id: string,
  |},
  +value?: ?string,
  +medias?: $ReadOnlyArray<{|
    +id: string,
    +name: string,
    +url: string,
    +size: string,
  |}>,
|}>;

type MultipleChoiceQuestionValue = {|
  labels: $ReadOnlyArray<string>,
  other: ?string,
|};

type ResponseInReduxForm = {|
  question: string,
  value:
    | MultipleChoiceQuestionValue
    | ReactSelectValue
    | ?string
    | ?number
    | $ReadOnlyArray<{|
        +id: string,
        +name: string,
        +url: string,
        +size: string,
      |}>,
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
type SubmitResponses = $ReadOnlyArray<{|
  value?: any,
  question: string,
  medias?: ?$ReadOnlyArray<string>,
|}>;

const IS_OPERATOR = 'IS';
const IS_NOT_OPERATOR = 'IS_NOT';

const mapQuestionChoicesToOptions = (question: Question) =>
  question.choices &&
  question.choices.edges &&
  question.choices.edges
    .filter(Boolean)
    .map(edge => edge.node)
    .filter(Boolean)
    .map(choice => ({
      value: choice.title,
      label: choice.title,
    }));

const getValueFromSubmitResponse = (response: ?ResponseInReduxForm): ?string => {
  if (response && typeof response.value === 'string') {
    return response.value;
  }
  if (
    response &&
    response.value &&
    typeof response.value === 'object' &&
    'value' in response.value &&
    'label' in response.value
  ) {
    // Here, we are dealing with a select question that uses `react-select`.
    // React select option choice must have the shape { value: xxx, label: xxx } in Redux to work
    // See https://www.firehydrant.io/blog/using-react-select-with-redux-form/ (part: `Other Gotchas`)
    return ((response.value: any): ReactSelectValue).value;
  }
  if (
    response &&
    response.value &&
    typeof response.value === 'object' &&
    !Array.isArray(response.value)
  ) {
    return ((response.value: any): MultipleChoiceQuestionValue).labels[0];
  }
  if (
    response &&
    response.value &&
    Array.isArray(response.value) &&
    response.value.filter(Boolean).length > 0
  ) {
    return typeof response.value[0] === 'object'
      ? response.value[0].name // Here, we are dealing with a MediaQuestion, which has the shape { id: string, name: string, url: string }
      : response.value.join(', '); // Here, we are dealing with a MultipleChoiceQuestion but more specifically a Ranking question, which is a simple array of strings
  }
  return null;
};

export const getValueFromResponse = (questionType: QuestionTypeValue, responseValue: string) => {
  // For some questions type we need to parse the JSON of previous value
  try {
    if (questionType === 'select') {
      // Here, we are dealing with a select question that uses `react-select`.
      // React select option choice must have the shape { value: xxx, label: xxx } in Redux to work
      // See https://www.firehydrant.io/blog/using-react-select-with-redux-form/ (part: `Other Gotchas`)
      return {
        label: responseValue,
        value: responseValue,
      };
    }
    if (questionType === 'number') {
      return Number(responseValue);
    }
    if (questionType === 'button') {
      return JSON.parse(responseValue).labels[0];
    }
    if (questionType === 'radio' || questionType === 'checkbox' || questionType === 'number') {
      return JSON.parse(responseValue);
    }
    if (questionType === 'ranking') {
      return JSON.parse(responseValue).labels;
    }
  } catch (e) {
    invariant(false, `Failed to parse: ${responseValue}`);
  }

  return responseValue;
};

export const formatInitialResponsesValues = (
  questions: Questions,
  responses: ResponsesFromAPI,
): ResponsesInReduxForm =>
  questions.map(question => {
    const response = responses.filter(res => res && res.question.id === question.id)[0];
    const questionId = question.id;

    // If we have a previous response format it
    if (response) {
      // TODO: response.value !== "null" is a hotfix, related to issue https://github.com/cap-collectif/platform/issues/6214
      // because of a weird bug, causing answer with questions set to "null" instead of NULL in db
      if (
        typeof response.value !== 'undefined' &&
        response.value !== null &&
        response.value !== 'null'
      ) {
        return {
          question: questionId,
          value: getValueFromResponse(question.type, response.value),
        };
      }
      if (typeof response.medias !== 'undefined') {
        return { question: questionId, value: response.medias };
      }
    }

    // Otherwise we create an empty response
    if (question.type === 'medias' || question.type === 'ranking') {
      return { question: questionId, value: [] };
    }
    if (question.type === 'radio' || question.type === 'checkbox') {
      return { question: questionId, value: { labels: [], other: null } };
    }

    return { question: questionId, value: null };
  });

const formattedChoicesInField = field =>
  field.choices &&
  field.choices.edges &&
  field.choices.edges
    .filter(Boolean)
    .map(edge => edge.node)
    .filter(Boolean)
    .map(choice => ({
      id: choice.id,
      label: choice.title,
      description: choice.description,
      color: choice.color,
      image: choice.image,
    }));

export const getRequiredFieldIndicationStrategy = (fields: Questions) => {
  const numberOfRequiredFields = fields.reduce((a, b) => a + (b.required ? 1 : 0), 0);
  const numberOfFields = fields.length;
  const halfNumberOfFields = numberOfFields / 2;
  if (numberOfRequiredFields === 0) {
    return 'no_required';
  }
  if (numberOfRequiredFields === numberOfFields) {
    return 'all_required';
  }
  if (numberOfRequiredFields === halfNumberOfFields) {
    return 'half_required';
  }
  if (numberOfRequiredFields > halfNumberOfFields) {
    return 'majority_required';
  }
  return 'minority_required';
};

const getResponseNumber = (value: any) => {
  if (typeof value === 'object' && Array.isArray(value.labels)) {
    const labelsNumber = value.labels.length;
    const hasOtherValue = value.other ? 1 : 0;
    return labelsNumber + hasOtherValue;
  }

  if (typeof value === 'object' && Array.isArray(value)) {
    return value.length;
  }

  return 0;
};

type ResponseError = ?{
  value: string | { labels: string, other: string },
};
type ResponseWarning = ?{
  value: string | { labels: string, other: string },
};

type ResponsesWarning = ResponseWarning[];
type ResponsesError = ResponseError[];

const hasAnsweredQuestion = (question: Question, responses: ResponsesInReduxForm): boolean => {
  const answer = responses.filter(Boolean).find(response => response.question === question.id);
  if (answer) {
    const submitResponse = getValueFromSubmitResponse(answer);
    return !!('value' in answer && submitResponse !== null && submitResponse !== '');
  }
  return false;
};

// alwaysJumpDestinationQuestion can be nullable, but when we call this method it is not
// null, we make verification about question.alwaysJumpDestinationQuestion, but flow does not recognize it
// Typically in this situation I would unwrap the value of question.alwaysJumpDestinationQuestion!, but
// unwrapping value does not seems to exists in flow
const createJumpFromAlwaysQuestion = (question: Question): Jump => ({
  // $FlowFixMe
  destination: question.alwaysJumpDestinationQuestion,
  conditions: [],
  origin: {
    id: question.id,
  },
  id: undefined,
});

const questionsHaveLogicJump = questions =>
  questions.reduce(
    (acc, question) => acc || (question && question.jumps && question.jumps.length > 0),
    false,
  );

// This method handle the condition return value (if a condition is considered fullfiled or not)
// based on the question type. Here we can add support for future questions types and handle
// their return logic here.
const getConditionReturn = (
  questionType: QuestionTypeValue,
  response: ?ResponseInReduxForm,
  condition: ConditionalJumpCondition,
  jump: Jump,
): boolean => {
  const userResponse = response && response.value;
  if (response && userResponse && condition.value) {
    const getFilteredCheckboxesConditions = (jumpCondition: ConditionalJumpCondition): boolean =>
      jumpCondition.question &&
      jumpCondition.question.type === 'checkbox' &&
      jumpCondition.question.id === response.question;

    switch (condition.operator) {
      case IS_OPERATOR:
        switch (questionType) {
          case 'select':
            // $FlowFixMe same as bottom :(
            return userResponse.value === condition.value.title;
          case 'ranking':
            // $FlowFixMe same as bottom :(
            return userResponse.includes(condition.value.title);
          case 'radio':
            // $FlowFixMe
            return (userResponse: MultipleChoiceQuestionValue).labels.includes(
              condition.value.title,
            );
          case 'checkbox':
            // Flow does not seem to understand the type casting here, because we know at
            // this point that userReponse is of MultipleChoiceQuestionValue but only in runtime
            return !!(
              jump.conditions &&
              userResponse.labels &&
              // $FlowFixMe
              jump.conditions.filter(Boolean).filter(getFilteredCheckboxesConditions).length ===
                (userResponse.labels &&
                  // $FlowFixMe
                  userResponse.labels.length) &&
              // $FlowFixMe
              (userResponse: MultipleChoiceQuestionValue).labels.includes(condition.value.title)
            );
          default:
            return condition.value.title === userResponse;
        }
      case IS_NOT_OPERATOR:
        switch (questionType) {
          case 'select':
            // $FlowFixMe same as bottom :(
            return userResponse.value !== condition.value.title;
          case 'ranking':
            // $FlowFixMe same as bottom :(
            return userResponse.includes(condition.value.title);
          case 'radio':
            // $FlowFixMe
            return (userResponse: MultipleChoiceQuestionValue).labels.includes(
              condition.value.title,
            );
          case 'checkbox':
            // Flow does not seem to understand the type casting here, because we know at
            // this point that userReponse is of MultipleChoiceQuestionValue but only in runtime
            return !!(
              jump.conditions &&
              userResponse.labels &&
              // $FlowFixMe
              jump.conditions.filter(Boolean).filter(getFilteredCheckboxesConditions).length ===
                (userResponse.labels &&
                  // $FlowFixMe
                  userResponse.labels.length) &&
              // $FlowFixMe
              !(userResponse: MultipleChoiceQuestionValue).labels.includes(condition.value.title)
            );
          default:
            return condition.value.title !== userResponse;
        }
      default:
        return false;
    }
  }
  return false;
};

export const getNextLogicJumpQuestion = (question: Question, questions: Questions): ?Question => {
  return (
    questions.slice(questions.indexOf(question) + 1).find(q => q.jumps && q.jumps.length > 0) ||
    null
  );
};

export const isAnyQuestionJumpsFullfilled = (
  question: Question,
  responses: ResponsesInReduxForm,
): boolean => {
  if (question.jumps) {
    return (
      question.jumps.filter(Boolean).some(jump =>
        jump.conditions
          ? jump.conditions.filter(Boolean).every(condition => {
              const answered = responses
                ? responses
                    .filter(Boolean)
                    .find(response => response.question === condition.question.id)
                : null;
              return getConditionReturn(condition.question.type, answered, condition, jump);
            })
          : false,
      ) || !!(question.alwaysJumpDestinationQuestion && hasAnsweredQuestion(question, responses))
    );
  }
  return !!(question.alwaysJumpDestinationQuestion && hasAnsweredQuestion(question, responses));
};

// This method is used to get a list of dependent questions (a question is dependant when
// it is present in the same branch tree of another question)
export const getQuestionDeps = (question: Question, questions: Questions): Questions =>
  questions
    .filter(Boolean)
    .filter(
      q =>
        (q.alwaysJumpDestinationQuestion && q.alwaysJumpDestinationQuestion.id === question.id) ||
        (q.jumps && q.jumps.filter(Boolean).some(jump => jump.destination.id === question.id)),
    );

const getOrphanedQuestions = (questions: Questions): Questions =>
  questions.reduce((acc, question) => {
    const deps = getQuestionDeps(question, questions);
    return [...acc, ...(deps.length === 0 ? [question] : [])];
  }, []);

// This method returns, for a given questions and based on user's answers, the list of fullfilled logic jumps
// (all the jumps where all the conditions have been met)
export const getFullfilledJumps = (question: Question, responses: ResponsesInReduxForm): Jump[] => {
  const elseJumps =
    question.alwaysJumpDestinationQuestion && hasAnsweredQuestion(question, responses)
      ? [createJumpFromAlwaysQuestion(question)]
      : [];
  if (question.jumps) {
    const fullfilleds = question.jumps.filter(Boolean).filter(jump =>
      jump.conditions
        ? jump.conditions.filter(Boolean).every(condition => {
            const answered = responses.find(
              response => response.question === condition.question.id,
            );
            return getConditionReturn(condition.question.type, answered, condition, jump);
          })
        : false,
    );
    // Here, one ore more conditions have been fullfilled, so we return them to show questions based on their conditions
    if (fullfilleds.length > 0) {
      return fullfilleds;
    }
    const answered = responses.find(response => response.question === question.id);
    if (answered && getValueFromSubmitResponse(answered)) {
      // Here, no conditions have been met and the user have correctly answered the question so we are in the "else" case
      return elseJumps;
    }
    // Here, no conditions have been met and the user have not answered the question so we show nothing more
    return [];
  }
  return [];
};

// This is the main method, used in `renderResponses` that returns, given the Questionnaire's questions and the
// user's answers, a list of questions ids that should be displayed to the user based on it's answers
// and the logic jumps defined for the questions
export const getAvailableQuestionsIds = (
  questions: Questions,
  responses: ResponsesInReduxForm,
): string[] => {
  // If no jump in questionnaire every question is available
  const hasLogicJumps = questionsHaveLogicJump(questions);
  if (!hasLogicJumps) {
    return questions.map(q => q.id);
  }

  // Otherwise let's calculate what is currently displayed to user…
  const firstLogicQuestion = questions.find(
    question =>
      (question.jumps && question.jumps.length > 0) || question.alwaysJumpDestinationQuestion,
  );

  // We need the first questions before the first logic jump of the questionnaire, so we display
  // them to the user
  const firstQuestionsIds = questions
    .slice(0, questions.indexOf(firstLogicQuestion) + 1)
    .map(question => question.id);

  // We get all the fullfilled questions ids (the questions that have met all the conditions)
  const fullfilledQuestionsIds = questions.reduce((acc, question) => {
    if (isAnyQuestionJumpsFullfilled(question, responses)) {
      const jumps = getFullfilledJumps(question, responses);
      const answers = jumps.map(
        jump => responses.find(response => response.question === jump.origin.id) || null,
      );
      const answer = answers.filter(Boolean).find(a => a.question === question.id) || null;
      if (
        jumps.length > 0 &&
        (responses.length === 0 || (answer && getValueFromSubmitResponse(answer) === null))
      ) {
        const visibleJumps = jumps.filter(Boolean).map(jump => jump.destination.id);

        return [...acc, ...visibleJumps];
      }
      // Because of how Logic Jump behaves, the first condition that’s met will trigger the Logic Jump
      // and prevent other scenarios when all the conditions of a jump concern the same question.
      // (e.g a checkbox question which has many conditions)
      // see https://www.typeform.com/help/single-branching-vs-multi-branching/
      const fullfilledJump = jumps
        .filter(Boolean)
        .filter(
          jump =>
            jump.conditions &&
            jump.conditions.length > 0 &&
            jump.conditions
              .filter(Boolean)
              .every(condition => condition.question.id === jump.origin.id),
        )[0];

      const visibleJumps = jumps.filter(
        jump =>
          jump.conditions &&
          (jump.conditions.length === 0 ||
            jump.conditions
              .filter(Boolean)
              .some(condition => condition.question.id !== jump.origin.id)),
      );

      return [
        ...acc,
        ...(fullfilledJump ? [fullfilledJump.destination.id] : []),
        ...visibleJumps.filter(Boolean).map(jump => jump.destination.id),
      ];
    }
    return acc;
  }, []);

  fullfilledQuestionsIds.map((qId: string) => questions.find(q => q.id === qId));

  const orphanedQuestionsIds = getOrphanedQuestions(questions).map(question => question.id);

  // $FlowFixMe
  return Array.from(
    new Set([...firstQuestionsIds, ...fullfilledQuestionsIds, ...orphanedQuestionsIds]),
  );
};

const getQuestionInitialValue = (question: Question) => {
  // Same value that function "formatInitialResponsesValues"
  if (question.type === 'medias' || question.type === 'ranking') {
    return [];
  }

  if (question.type === 'radio' || question.type === 'checkbox') {
    return { labels: [], other: null };
  }

  return null;
};

export const validateResponses = (
  questions: Questions,
  responses: ResponsesInReduxForm,
  // TODO: remove this parameter from the function and create generic traduction keys for all errors.
  className: string,
  intl: IntlShape,
  // The behavior of the validator depends on the draft value of the response.
  isDraft: boolean = false,
): { responses?: ResponsesError } => {
  const availableQuestionIds = getAvailableQuestionsIds(questions, responses);

  const responsesError = questions
    .filter(question => availableQuestionIds.includes(question.id))
    .map(question => {
      const response = responses.filter(res => res && res.question === question.id)[0];

      if (question.required && !isDraft) {
        if (question.type === 'medias') {
          if (!response || (Array.isArray(response.value) && response.value.length === 0)) {
            return { value: `${className}.constraints.field_mandatory` };
          }
        } else if (
          question.type === 'checkbox' &&
          JSON.stringify(response.value) === JSON.stringify(getQuestionInitialValue(question))
        ) {
          if (
            !response ||
            (response.value &&
              Array.isArray(response.value.labels) &&
              response.value.labels.length === 0 &&
              response.value.other === null &&
              !isDraft)
          ) {
            return { value: `${className}.constraints.field_mandatory` };
          }
        } else if (question.type === 'radio') {
          if (
            !response ||
            (response.value &&
              Array.isArray(response.value.labels) &&
              response.value.labels.length === 0 &&
              (response.value.other === null || response.value.other === '') &&
              !isDraft)
          ) {
            return { value: `${className}.constraints.field_mandatory` };
          }
        } else if (question.type === 'editor') {
          if (
            !response ||
            !response.value ||
            (response.value &&
              typeof response.value === 'string' &&
              stripHtml(response.value).length === 0)
          ) {
            return { value: `${className}.constraints.field_mandatory` };
          }
        } else if (
          !response ||
          !response.value ||
          JSON.stringify(response.value) === JSON.stringify(getQuestionInitialValue(question))
        ) {
          return { value: `${className}.constraints.field_mandatory` };
        }
      }

      if (
        question.type === 'number' &&
        response.value &&
        typeof response.value === 'string' &&
        !checkOnlyNumbers(response.value)
      ) {
        return { value: `please-enter-a-number` };
      }

      if (
        question.validationRule &&
        question.type !== 'button' &&
        response.value &&
        typeof response.value === 'object' &&
        ((Array.isArray(response.value.labels) && response.value.labels.length > 0) ||
          (Array.isArray(response.value) && response.value.length > 0) ||
          response.value.other) &&
        !isDraft
      ) {
        const rule = question.validationRule;
        const responsesNumber = getResponseNumber(response.value);
        if (rule.type === 'MIN' && rule.number && responsesNumber < rule.number) {
          return {
            value: intl.formatMessage({ id: 'reply.constraints.choices_min' }, { nb: rule.number }),
          };
        }

        if (rule.type === 'MAX' && rule.number && responsesNumber > rule.number) {
          return {
            value: intl.formatMessage({ id: 'reply.constraints.choices_max' }, { nb: rule.number }),
          };
        }

        if (rule.type === 'EQUAL' && responsesNumber !== rule.number) {
          return {
            value: intl.formatMessage(
              { id: 'reply.constraints.choices_equal' },
              { nb: rule.number },
            ),
          };
        }
      }
    });

  return responsesError && responsesError.length ? { responses: responsesError } : {};
};

export const formatSubmitResponses = (
  responses: ?ResponsesInReduxForm,
  questions: Questions,
): SubmitResponses => {
  if (!responses) return [];
  const answeredQuestionsIds = getAvailableQuestionsIds(questions, responses);
  return responses.map(res => {
    const question = questions.filter(q => res.question === q.id)[0];
    const { type: questionType } = question;

    if (questionType === 'medias') {
      const medias = answeredQuestionsIds.includes(question.id)
        ? Array.isArray(res.value)
          ? res.value.map(value => value.id)
          : []
        : null;
      return {
        question: res.question,
        medias,
      };
    }
    let { value } = res;
    if (questionType === 'select') {
      // Here, we are dealing with a select question that uses `react-select`.
      // React select option choice must have the shape { value: xxx, label: xxx } in Redux to work
      // See https://www.firehydrant.io/blog/using-react-select-with-redux-form/ (part: `Other Gotchas`)
      return {
        question: res.question,
        value: value ? ((value: any): ReactSelectValue).value : null,
      };
    }
    if (questionType === 'ranking' || questionType === 'button') {
      value = answeredQuestionsIds.includes(question.id)
        ? JSON.stringify({
            labels: Array.isArray(res.value) ? res.value : [res.value].filter(Boolean),
            other: null,
          })
        : null;
    } else if (questionType === 'checkbox' || questionType === 'radio') {
      value = answeredQuestionsIds.includes(question.id) ? JSON.stringify(res.value) : null;
    } else if (questionType === 'number') {
      return {
        question: res.question,
        value: res.value,
      };
    }
    if (typeof value === 'string') {
      value = answeredQuestionsIds.includes(question.id) ? value : null;
      return { value, question: res.question };
    }
    return { value: null, question: res.question };
  });
};

export const warnResponses = (
  questions: Questions,
  responses: ResponsesInReduxForm,
  // TODO: remove this parameter from the function and create generic traduction keys for all errors.
  className: string,
  intl: IntlShape,
  isDraft: boolean = false,
): { responses?: ResponsesWarning } => {
  return validateResponses(questions, responses, className, intl, !isDraft);
};

export const renderResponses = ({
  fields,
  questions,
  responses,
  intl,
  form,
  change,
  disabled,
  typeForm = TYPE_FORM.DEFAULT,
}: {
  ...FieldArrayProps,
  questions: Questions,
  responses: ResponsesInReduxForm,
  change: (field: string, value: any) => void,
  form: string,
  intl: IntlShape,
  disabled: boolean,
  typeForm?: $Values<typeof TYPE_FORM>,
}) => {
  const strategy = getRequiredFieldIndicationStrategy(questions);
  const availableQuestions = getAvailableQuestionsIds(questions, responses);

  // modif
  const notAvailableQuestions = questions
    .filter(Boolean)
    .filter(question => !availableQuestions.includes(question.id))
    .map(question => question.id);

  notAvailableQuestions.forEach((notAvailableQuestion: string) => {
    const question = questions.find(q => q.id === notAvailableQuestion);
    if (question) {
      const indexInRedux = questions.indexOf(question);
      const responseCurrentQuestion =
        responses &&
        responses.find(
          ({ question: questionResponse }) => questionResponse === notAvailableQuestion,
        );

      // reset response only for not available question
      if (
        responseCurrentQuestion &&
        getQuestionInitialValue(question) !== responseCurrentQuestion.value
      ) {
        change(`responses[${indexInRedux}].value`, getQuestionInitialValue(question));
      }
    }
  });

  return (
    <div>
      {fields &&
        fields.map((member, index) => {
          const field = questions[index];
          let isAvailableQuestion = true;

          if (!availableQuestions.includes(field.id)) {
            isAvailableQuestion = false;
          }

          const { isOtherAllowed } = field;

          const labelAppend = field.required
            ? strategy === 'minority_required'
              ? ` <span class="warning small"> ${intl.formatMessage({
                  id: 'global.mandatory',
                })}</span>`
              : ''
            : strategy === 'majority_required' || strategy === 'half_required'
            ? ` <span class="excerpt small"> ${intl.formatMessage({
                id: 'global.optional',
              })}</span>`
            : '';

          const labelMessage = field.title + labelAppend;

          const label = (
            <React.Fragment>
              {field.number && <span className="visible-print-block">{field.number}.</span>}{' '}
              <span dangerouslySetInnerHTML={{ __html: labelMessage }} />
            </React.Fragment>
          );

          switch (field.type) {
            case 'section': {
              return (
                <div key={field.id} className={isAvailableQuestion === false ? 'visible-print-block form__section' : "form__section"}>
                  <TitleInvertContrast>{field.title}</TitleInvertContrast>
                  <div className="mb-15">
                    <WYSIWYGRender value={field.description} />
                  </div>
                  {/* Hack: we render an input for sections in developement, to make logic jump work */}
                  {
                    config.isDev && field.alwaysJumpDestinationQuestion ? (<Field
                      name={`${member}.value`}
                      id={`${cleanDomId(`${form}-${member}`)}`}
                      type="text"
                      placeholder="Je suis un hack pour faire marcher les jumps sur les sections. Remplissez moi c'est magique."
                      /* $FlowFixMe */
                      component={component}
                  />) : null
                  }
                </div>
              );
            }
            case 'medias': {
              return (
                <div
                  key={field.id}
                  className={isAvailableQuestion === false ? 'visible-print-block' : ''}>
                  <PrivateBox show={field.private}>
                    <Field
                      name={`${member}.value`}
                      id={`${cleanDomId(`${form}-${member}`)}`}
                      type="medias"
                      // $FlowFixMe
                      component={component}
                      help={field.helpText}
                      description={field.description}
                      placeholder="reply.your_response"
                      label={label}
                      disabled={disabled}
                      typeForm={typeForm}
                    />
                    {/* $FlowFixMe please fix this */}
                    <ConditionalJumps jumps={field.jumps} />
                  </PrivateBox>
                </div>
              );
            }
            case 'select': {
              if (!('choices' in field)) return null;
              const loadOptions = (term: string) =>
                new Promise(async resolve => {
                  const response = await fetchQuery(
                    environment,
                    MULTIPLE_QUESTION_CHOICES_SEARCH_QUERY,
                    {
                      questionId: field.id,
                      term,
                    },
                  );
                  resolve(mapQuestionChoicesToOptions(response.node));
                });
              const needsSearch =
                field.choices &&
                field.choices.totalCount > MULTIPLE_QUESTION_CHOICES_COUNT_TRIGGER_SEARCH;
              const fieldProps = needsSearch
                ? {
                    debounce: true,
                    loadOptions,
                    cacheOptions: true,
                    autoload: mapQuestionChoicesToOptions(field),
                  }
                : {
                    cacheOptions: true,
                    options: mapQuestionChoicesToOptions(field),
                  };
              return (
                <div
                  key={field.id}
                  className={isAvailableQuestion === false ? 'visible-print-block' : ''}>
                  <PrivateBox show={field.private}>
                    <Field
                      divClassName="reduced"
                      name={`${member}.value`}
                      id={`${cleanDomId(`${form}-${member}`)}`}
                      type={field.type}
                      component={select}
                      help={field.helpText}
                      isOtherAllowed={isOtherAllowed}
                      description={field.description}
                      label={label}
                      placeholder={intl.formatMessage({ id: 'reply.your_response' })}
                      disabled={disabled}
                      typeForm={typeForm}
                      {...fieldProps}
                      selectFieldIsObject
                    />
                    <div className="visible-print-block form-fields">
                      {field.choices &&
                        field.choices.edges &&
                        field.choices.edges
                          .filter(Boolean)
                          .map(edge => edge.node)
                          .filter(Boolean)
                          .map(choice => (
                            <div key={choice.id} className="radio">
                              {choice.title}
                            </div>
                          ))}
                    </div>
                    {/* $FlowFixMe please fix this */}
                    <ConditionalJumps jumps={field.jumps} />
                  </PrivateBox>
                </div>
              );
            }
            default: {
              const response =
                responses && responses[index] && responses[index].value
                  ? responses[index].value
                  : null;
              let choices = [];
              if (
                field.type === 'ranking' ||
                field.type === 'radio' ||
                field.type === 'checkbox' ||
                field.type === 'button'
              ) {
                choices = formattedChoicesInField(field);
              }

              return (
                <div
                  key={field.id}
                  className={isAvailableQuestion === false ? 'visible-print-block' : ''}>
                  <PrivateBox show={field.private}>
                    <Field
                      divClassName="reduced"
                      name={`${member}.value`}
                      id={`${cleanDomId(`${form}-${member}`)}`}
                      type={field.type}
                      // $FlowFixMe
                      component={component}
                      description={field.description}
                      help={field.helpText}
                      isOtherAllowed={isOtherAllowed}
                      placeholder="reply.your_response"
                      choices={choices}
                      label={label}
                      disabled={disabled}
                      value={response}
                      typeForm={typeForm}
                    />
                  </PrivateBox>
                </div>
              );
            }
          }
        })}
    </div>
  );
};

// This whole file is a mess and should be refactored, I have no choice but to put an any for now
export const formatChoices = (
  questionnaire: QuestionnaireAdminConfigurationForm_questionnaire,
): any => {
  const questions = questionnaire.questions.map(question => {
    if (question.__typename !== 'MultipleChoiceQuestion') return question;
    const choices =
      question.choices && question.choices.edges
        ? question.choices.edges
            .filter(Boolean)
            .map(edge => edge.node)
            .filter(Boolean)
        : [];
    return { ...question, choices };
  });
  return { ...questionnaire, questions };
};
