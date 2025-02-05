// @flow
import * as React from 'react';
import styled, { type StyledComponent } from 'styled-components';
import { Circle } from 'styled-spinkit';
import colors from '../../../utils/colors';

type Props = {|
  +show: boolean,
  +inline: boolean,
  +size: number,
  +color: string,
  +id?: string,
  +children?: ?React.Node,
|};

export const Container: StyledComponent<{ inline?: boolean }, {}, HTMLDivElement> = styled.div`
  text-align: center;
  display: ${props => (props.inline ? 'inline-flex' : 'flex')};
  ${props =>
    !props.inline &&
    `padding-top: 50px;
      padding-bottom: 30px;`};
  width: 100%;

  div {
    margin: auto;
  }
`;

export class Loader extends React.Component<Props> {
  static defaultProps = {
    show: true,
    inline: false,
    size: 40,
    color: colors.darkText,
  };

  render() {
    const { children, show, inline, size, color, id } = this.props;
    if (show) {
      return (
        <Container id={id} className="loader" inline={inline}>
          <Circle size={size} color={color} />
        </Container>
      );
    }
    if (!children) {
      return null;
    }
    return Array.isArray(children) ? <div>{children}</div> : children;
  }
}

export default Loader;
