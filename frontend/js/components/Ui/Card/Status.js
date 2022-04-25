// @flow
import styled, { type StyledComponent } from 'styled-components';
import * as React from 'react';
import { BsStyleColors } from '../../../utils/colors';
import type { ProposalStepStatusColor } from '~relay/ProposalCollectStatus_proposal.graphql';

type Props = {
  bgColor: ProposalStepStatusColor,
  children: React.Node,
};

const Container: StyledComponent<Props, {}, HTMLDivElement> = styled.div.attrs({
  className: props =>
    props.bsStyle === 'PRIMARY'
      ? 'ellipsis card__status custom-primary-bgcolor'
      : 'ellipsis card__status',
})`
  border-bottom-right-radius: 3px;
  border-bottom-left-radius: 3px;
  padding: 3px;
  min-height: 25px;
  color: white;
  font-size: 14px;
  text-align: center;
  background-color: ${props => props.bgColor};
`;

export const Status = (props: Props) => {
  const { bgColor, children } = props;

  const getBgColor = BsStyleColors[bgColor];

  return (
    <Container bsStyle={bgColor} bgColor={getBgColor}>
      {children}
    </Container>
  );
};

Status.defaultProps = {
  bgColor: 'DEFAULT',
};

export default Status;
