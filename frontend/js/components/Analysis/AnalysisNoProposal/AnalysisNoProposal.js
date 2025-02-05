// @flow
import * as React from 'react';
import { FormattedMessage, useIntl } from 'react-intl';
import Icon, { ICON_NAME } from '~ui/Icons/Icon';
import colors from '~/utils/colors';
import AnalysisNoProposalContainer from './AnalysisNoProposal.style';

const TYPE_STATE = {
  ANALYSIS: {
    icon: ICON_NAME.messageBubble,
    idWording: 'help.title.section.empty',
    variableWording: 'panel.analysis.subtitle',
  },
  CONTRIBUTION: {
    icon: ICON_NAME.messageBubble,
    idWording: 'proposition.list.help.title.no.search.result',
  },
  ALL: {
    icon: ICON_NAME.messageBubble,
    idWording: 'help.title.section.empty',
    variableWording: 'global-all',
  },
  PUBLISHED: {
    icon: ICON_NAME.messageBubble,
    idWording: 'help.title.section.empty',
    variableWording: 'admin.fields.proposal.state.choices.published',
  },
  DRAFT: {
    icon: ICON_NAME.draft,
    idWording: 'help.title.section.empty',
    variableWording: 'proposal.state.draft',
  },
  TRASHED: {
    icon: ICON_NAME.trash2,
    idWording: 'help.title.section.bin.empty',
  },
  DONE: {
    icon: ICON_NAME.messageBubble,
    idWording: 'help.title.section.empty',
    variableWording: 'global.done',
  },
  TODO: {
    icon: ICON_NAME.messageBubbleCheck,
    idWording: 'help.title.all.proposition.analysed',
  },
};

type Props = {|
  +state: $Keys<typeof TYPE_STATE>,
  +children: React.Node,
|};

const AnalysisNoProposal = ({ state, children }: Props) => {
  const intl = useIntl();
  const currentState = TYPE_STATE[state];

  return (
    <AnalysisNoProposalContainer>
      <Icon name={currentState.icon} size={50} color={colors.darkGray} />

      {currentState.variableWording ? (
        <FormattedMessage
          id={currentState.idWording}
          values={{
            tabTitle: intl.formatMessage({ id: currentState.variableWording }),
          }}
          tagName="p"
        />
      ) : (
        <FormattedMessage id={currentState.idWording} tagName="p" />
      )}

      {children}
    </AnalysisNoProposalContainer>
  );
};

export default AnalysisNoProposal;
