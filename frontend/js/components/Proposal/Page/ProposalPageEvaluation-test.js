// @flow
/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';
import { ProposalPageEvaluation } from './ProposalPageEvaluation';
import { intlMock, formMock, $refType } from '../../../mocks';

describe('<ProposalPageEvaluation />', () => {
  const proposal = {
    $refType,
    id: 'proposal1',
    viewerIsAnEvaluer: true,
    form: {
      evaluationForm: {
        description: null,
        questions: [
          {
            id: 'question1',
            title: 'Question 1',
            position: 0,
            number: 0,
            private: false,
            required: true,
            hidden: false,
            helpText: null,
            alwaysJumpDestinationQuestion: null,
            jumps: [],
            destinationJumps: [],
            description: null,
            type: 'text',
            isOtherAllowed: false,
            validationRule: null,
            __typename: 'SimpleQuestion',
            choices: {
              pageInfo: {
                hasNextPage: false,
              },
              totalCount: 0,
              edges: [],
            },
          },
        ],
      },
    },
    evaluation: {
      version: 1,
      responses: [
        {
          question: { id: 'question1' },
          value: 'Paul',
        },
      ],
    },
  };

  const props = {
    ...formMock,
    proposal,
    intl: intlMock,
    responses: [],
  };

  it('render a form if viewer is an evaluer', () => {
    const wrapper = shallow(<ProposalPageEvaluation {...props} />);
    expect(wrapper).toMatchSnapshot();
  });

  const propsDisabled = {
    proposal: { ...proposal, viewerIsAnEvaluer: false },
    ...formMock,
    intl: intlMock,
    responses: [],
  };

  it('render a disabled form if viewer is not an evaluer', () => {
    const wrapper = shallow(<ProposalPageEvaluation {...propsDisabled} />);
    expect(wrapper).toMatchSnapshot();
  });
});
